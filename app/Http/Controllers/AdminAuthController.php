<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminRegisterLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::with('rights')->where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
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

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => $admin,
            'role' => $admin->role,
            'rights' => $admin->rights
        ]);
    }

    public function createAdmin(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email|unique:admins,email',
            'role' => ['required', Rule::in(['admin', 'support', 'manager'])],
            'rights' => 'required|array',
        ]);

        // Create the admin
        $admin = Admin::create([
            'email' => $request->email,
            'role' => $request->role,
            'status' => 'pending',
        ]);

        // Create the admin rights
        $admin->rights()->create($request->rights);

        // Generate a unique UUID
        $uuid = Str::uuid();

        // Create the registration link
        $registerLink = AdminRegisterLink::create([
            'admin_id' => $admin->id,
            'uuid' => $uuid,
        ]);

        // Send the registration email
        $this->sendRegistrationEmail($admin->email, $uuid);

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => $admin,
            'registration_link' => env('FRONTEND_URL') . '/admin-register/' . $uuid
        ], 201);
    }

    private function sendRegistrationEmail($email, $uuid)
    {
        // Here you would implement your email sending logic using Laravel's Mail facade
        // For the purpose of this code, we'll just return a placeholder

        $registrationLink = env('FRONTEND_URL') . '/admin-register/' . $uuid;

        // Uncomment and configure according to your email setup
        /*
        Mail::send('emails.admin-registration', ['registrationLink' => $registrationLink], function ($message) use ($email) {
            $message->to($email)
                    ->subject('Admin Registration - Notes for Therapy');
        });
        */

        // For now, we'll just log the link (you would remove this in production)
        Log::info('Admin registration link for ' . $email . ': ' . $registrationLink);
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
        ]);

        // Delete the registration link
        $registerLink->delete();

        return response()->json([
            'message' => 'Registration completed successfully',
            'admin' => $admin
        ]);
    }

    public function getAdmins()
    {
        $admins = Admin::with('rights')->get();

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
        $admin->update([
            'role' => $request->role,
        ]);

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

        // Update the admin's rights
        $admin->rights()->update($request->all());

        return response()->json([
            'message' => 'Admin permissions updated successfully',
            'admin' => $admin->load('rights')
        ]);
    }

    public function removeAdmin(Request $request)
    {
        // Validate the request
        $request->validate([
            'admin_id' => 'required|exists:admins,id',
        ]);

        // Check if the admin is trying to delete themselves
        if ($request->admin_id == auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        // Delete the admin
        $admin = Admin::findOrFail($request->admin_id);
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
}
