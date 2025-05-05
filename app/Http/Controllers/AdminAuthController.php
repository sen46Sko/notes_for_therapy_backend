<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminDevice;
use App\Models\AdminActivityLog;
use App\Models\FailedLoginAttempt;
use App\Models\AdminRegisterLink;
use App\Mail\AdminRegistrationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Services\AdminTwoFactorService;
use App\Services\PasswordPolicyService;
use Jenssegers\Agent\Agent;
use Illuminate\Validation\ValidationException;


class AdminAuthController extends Controller
{

    protected $twoFactorService;
    protected $passwordPolicyService;

    public function __construct(
        AdminTwoFactorService $twoFactorService,
        PasswordPolicyService $passwordPolicyService
    ) {
        $this->twoFactorService = $twoFactorService;
        $this->passwordPolicyService = $passwordPolicyService;
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::with('rights')->where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            $this->logFailedLogin($request);

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if admin is deactivated
        if ($admin->isDeactivated()) {
            return response()->json([
                'message' => 'Your account is deactivated until ' . $admin->deactivate_to->format('Y-m-d H:i:s')
            ], 403);
        }

        // Check if admin status is pending
        if ($admin->status === 'pending') {
            return response()->json([
                'message' => 'Your account is pending activation'
            ], 403);
        }

        $admin->update(['last_activity_at' => now()]);

        $device = $this->trackDevice($admin, $request);

        $this->logActivity($admin, 'login', 'Admin logged in', $request);

        if ($admin->two_factor_enabled) {

            $this->twoFactorService->generateAndSendCode($admin);

            return response()->json([
                'message' => 'Two-factor authentication required',
                'requires_2fa' => true,
                'email' => $admin->email
            ]);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;


        $adminData = $admin->toArray();

        if (isset($adminData['avatar'])) {
            $adminData['avatar'] = $admin->avatar_url;
        }

        return response()->json([
            'token' => $token,
            'admin' => $admin,
            'role' => $admin->role,
            'rights' => $admin->rights,
            'device_id' => $device->id,
        ]);
    }


    public function createAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email',
            'role' => ['required', Rule::in(['admin', 'support', 'manager'])],
            'rights' => 'required|array',
            'name' => 'sometimes|string|max:255'
        ]);

        $admin = Admin::create([
            'email' => $request->email,
            'role' => $request->role,
            'status' => 'pending',
            'name' => $request->name ?? null,
            'password' => Hash::make(Str::random(40)),
        ]);

        $rights = array_map(function($value) {
            return $value === true;
        }, $request->rights);

        $admin->rights()->create($rights);

        $uuid = Str::uuid();

        AdminRegisterLink::create([
            'admin_id' => $admin->id,
            'uuid' => $uuid,
        ]);

        try {
            $this->sendRegistrationEmail($admin->email, $uuid);

            return response()->json([
                'message' => 'Admin created successfully and invitation sent',
                'admin' => $admin,
                'registration_link' => env('FRONTEND_URL', 'http://localhost:5173') . '/admin-register/' . $uuid
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to send registration email: ' . $e->getMessage());

            return response()->json([
                'message' => 'Admin created, but failed to send invitation email',
                'admin' => $admin,
                'error' => $e->getMessage()
            ], 206);
        }
    }

private function sendRegistrationEmail($email, $uuid)
{
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
    $registrationLink = $frontendUrl . '/admin-register/' . $uuid;

    Log::info('Sending registration email with link: ' . $registrationLink);

    try {
        Mail::to($email)->send(new AdminRegistrationMail($registrationLink));
        Log::info('Registration email sent to ' . $email);
    } catch (\Exception $e) {
        Log::error('Failed to send registration email: ' . $e->getMessage());
        throw $e;
    }
}



    public function confirmRegister(Request $request)
        {
            // Validate the request
            $request->validate([
                'uuid' => 'required|string|exists:admin_register_links,uuid',
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:8',
                'confirmPassword' => 'required|same:password',
            ]);

            // Validate password against policy
            $passwordErrors = $this->passwordPolicyService->validatePassword($request->password);
            if (!empty($passwordErrors)) {
                throw ValidationException::withMessages([
                    'password' => $passwordErrors,
                ]);
            }

            // Find the registration link
            $registerLink = AdminRegisterLink::where('uuid', $request->uuid)->first();

            if (!$registerLink) {
                return response()->json([
                    'message' => 'Invalid registration link'
                ], 404);
            }

            // Update the admin
            $admin = Admin::find($registerLink->admin_id);
            $admin->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'password_changed_at' => now(), // Set initial password change date
            ]);

            // Delete the registration link
            $registerLink->delete();

            // Notify other admins about the new admin
            $otherAdmins = Admin::where('id', '!=', $admin->id)->get();
            foreach ($otherAdmins as $otherAdmin) {
                AdminNotificationController::createNotification(
                    $otherAdmin->id,
                    'user_notification',
                    'new_user',
                    'New Admin Registration',
                    "A new admin has completed registration: {$admin->name} ({$admin->email})"
                );
            }

            return response()->json([
                'message' => 'Registration completed successfully',
                'admin' => $admin
            ]);
        }


    public function getAdmins(Request $request)
    {
        $query = Admin::with('rights');

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('role')) {
            $role = $request->input('role');
            $query->where('role', $role);
        }

        $admins = $query->get();

        return response()->json([
            'admins' => $admins
        ]);
    }


    public function updateAdminRole(Request $request, $id)
    {
        // Check if the admin has the right to assign roles
        $currentAdmin = auth()->user();

        if (!$currentAdmin->rights->assign_roles) {
            return response()->json([
                'message' => 'You do not have permission to assign roles'
            ], 403);
        }

        // Validate the request
        $request->validate([
            'role' => ['required', Rule::in(['admin', 'support', 'manager'])],
        ]);

        // Update the admin's role
        $admin = Admin::findOrFail($id);
        $oldRole = $admin->role;
        $admin->update([
            'role' => $request->role,
        ]);

        $admins = Admin::where('id', '!=', $currentAdmin->id)->get();
        foreach ($admins as $notifiedAdmin) {
            AdminNotificationController::createNotification(
                $notifiedAdmin->id,
                'user_notification',
                'permission_change',
                'Admin Role Changed',
                "Admin {$admin->name} ({$admin->email}) role changed from {$oldRole} to {$request->role} by {$currentAdmin->name}"
            );
        }

        return response()->json([
            'message' => 'Admin role updated successfully',
            'admin' => $admin
        ]);
    }

    public function updateAdminPermission(Request $request, $id)
    {
        // Check if the admin has the right to modify permissions
        $currentAdmin = auth()->user();

        if (!$currentAdmin->rights->modify_permissions) {
            return response()->json([
                'message' => 'You do not have permission to modify permissions'
            ], 403);
        }

        // Find the admin
        $admin = Admin::findOrFail($id);
        $adminRights = $admin->rights;

        $changedPermissions = [];

        foreach ($request->all() as $key => $value) {
            if ($adminRights->$key !== $value) {
                $oldValue = $adminRights->$key ? 'enabled' : 'disabled';
                $newValue = $value ? 'enabled' : 'disabled';
                $changedPermissions[] = "$key: $oldValue → $newValue";
                $adminRights->$key = $value;
            }
        }

        $adminRights->timestamps = false;
        $adminRights->save();


        if (!empty($changedPermissions)) {
            $changedPermissionsText = implode(", ", $changedPermissions);

            // Notify other admins about the permission change
            $admins = Admin::where('id', '!=', $currentAdmin->id)->get();
            foreach ($admins as $notifiedAdmin) {
                AdminNotificationController::createNotification(
                    $notifiedAdmin->id,
                    'user_notification',
                    'permission_change',
                    'Admin Permissions Changed',
                    "Admin {$admin->name} ({$admin->email}) permissions changed by {$currentAdmin->name}. Changed: $changedPermissionsText"
                );
            }
        }

        return response()->json([
            'message' => 'Admin permissions updated successfully',
            'admin' => $admin->load('rights')
        ]);
    }




    public function removeAdmin(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:admins,id',
        ]);

        $currentAdmin = auth()->user();

        if (!$currentAdmin->rights->modify_permissions) {
            return response()->json([
                'message' => 'You do not have permission to remove admins'
            ], 403);
        }

        $adminId = $request->input('admin_id');

        if ($adminId == auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        $admin = Admin::findOrFail($adminId);
        if ($admin->role === 'super_admin') {
            return response()->json([
                'message' => 'You cannot delete a super admin'
            ], 403);
        }

        $admin->delete();

        return response()->json([
            'message' => 'Admin deleted successfully'
        ]);
    }



    public function deactivateAdmin(Request $request)
    {
        // Validate the request
        $request->validate([
            'admin_id' => 'required|exists:admins,id',
            'date' => 'required|date|after:now',
        ]);

        // Check if the admin is trying to deactivate themselves
        if ($request->admin_id == auth()->id()) {
            return response()->json([
                'message' => 'You cannot deactivate your own account'
            ], 403);
        }

        // Deactivate the admin
        $admin = Admin::findOrFail($request->admin_id);
        $admin->update([
            'deactivate_to' => $request->date,
        ]);

        return response()->json([
            'message' => 'Admin deactivated successfully until ' . $admin->deactivate_to->format('Y-m-d H:i:s'),
            'admin' => $admin
        ]);
    }

    public function activateAdmin(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:admins,id',
        ]);

        if ($request->admin_id == auth()->id()) {
            return response()->json([
                'message' => 'No need to activate admin account'
            ], 403);
        }

        $admin = Admin::findOrFail($request->admin_id);
        $admin->update([
            'deactivate_to' => null,
        ]);

        return response()->json([
            'message' => 'Account was successfully activated',
            'admin' => $admin
        ]);
    }


    public function updateProfile(Request $request)
        {
            $admin = auth()->user();

            $validatedData = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('admins')->ignore($admin->id),
                ],
            ]);

            $admin->name = $validatedData['full_name'];
            $admin->email = $validatedData['email'];

            if ($request->hasFile('avatar')) {
                $request->validate([
                    'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                if ($admin->avatar) {
                    Storage::disk('public')->delete($admin->avatar);
                }

                $path = $request->file('avatar')->store('avatars/admin', 'public');
                $admin->avatar = $path;
            }

            $admin->save();

            return response()->json([
                'full_name' => $admin->name,
                'email' => $admin->email,
                'avatar' => $admin->avatar,
            ]);
        }

    public function logout(Request $request)
        {
            $admin = auth()->user();

            if ($admin) {
                $this->logActivity($admin, 'logout', 'Admin logged out', $request);

                if ($request->device_id) {
                    AdminDevice::where('id', $request->device_id)
                        ->where('admin_id', $admin->id)
                        ->update(['is_active' => false]);
                }
            }

            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        }


    public function validateRegistrationLink($uuid)
    {
        $link = AdminRegisterLink::where('uuid', $uuid)->first();

        if (!$link) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid or expired registration link'
                ], 404);
            }

            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/error?message=Invalid+or+expired+registration+link');
        }

        if (request()->expectsJson()) {
            return response()->json([
                'email' => $link->admin->email,
                'message' => 'Registration link is valid'
            ]);
        }

        return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/admin-register/' . $uuid);
    }
    protected function trackDevice($admin, Request $request)
        {
            $agent = new Agent();
            $agent->setUserAgent($request->header('User-Agent'));

            $deviceType = $this->getDeviceType($agent);
            $ipAddress = $request->ip();

            $device = AdminDevice::firstOrCreate(
                [
                    'admin_id' => $admin->id,
                    'ip_address' => $ipAddress,
                    'device_type' => $deviceType,
                    'user_agent' => $request->header('User-Agent'),
                ],
                [
                    'device_name' => $this->getDeviceName($agent),
                    'last_active_at' => now(),
                    'is_active' => true,
                    'is_blocked' => false,
                ]
            );

            $device->update([
                'last_active_at' => now(),
                'is_active' => true,
            ]);

            return $device;
        }

        protected function logFailedLogin(Request $request)
        {
            $agent = new Agent();
            $agent->setUserAgent($request->header('User-Agent'));

            FailedLoginAttempt::create([
                'email' => $request->email,
                'device_type' => $this->getDeviceType($agent),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'attempted_at' => now(),
            ]);

            // Notify admins about failed login attempt
                $admins = Admin::all();
                foreach ($admins as $admin) {
                    AdminNotificationController::createNotification(
                        $admin->id,
                        'user_notification',
                        'failed_login',
                        'Failed Login Attempt',
                        "Failed login attempt for email {$request->email} from IP {$request->ip()} using {$this->getDeviceName($agent)}"
                    );
                }

        }

        protected function logActivity($admin, $action, $description = null, Request $request)
        {
            $agent = new Agent();
            $agent->setUserAgent($request->header('User-Agent'));

            AdminActivityLog::create([
                'admin_id' => $admin->id,
                'device_type' => $this->getDeviceType($agent),
                'ip_address' => $request->ip(),
                'action' => $action,
                'description' => $description,
                'created_at' => now(),
            ]);
        }

        protected function getDeviceType($agent)
        {
            if ($agent->isDesktop()) {
                return 'pc';
            } elseif ($agent->isMobile()) {
                return 'mobile';
            } elseif ($agent->isTablet()) {
                return 'tablet';
            }
            return 'pc';
        }

        protected function getDeviceName($agent)
        {
            $platform = $agent->platform();
            $browser = $agent->browser();
            return "{$platform} - {$browser}";
        }


}
