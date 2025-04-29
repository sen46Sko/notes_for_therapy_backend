<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Helper\StorageHelper;
use App\Mail\NewUserEmail;
use App\Models\Coupon;
use App\Models\Goal;
use App\Models\GoalTemplate;
use App\Models\GoalTracking;
use App\Models\Homework;
use App\Models\HomeworkModel;
use App\Models\HomeworkTemplate;
use App\Models\Mood;
use App\Models\Note;
use App\Models\Onboarding;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\Symptom;
use App\Models\Tracking;
use App\Models\TwoFactorAuth;
use App\Models\UsedCoupon;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\UserExperience;
use App\Models\UserSymptom;
use App\Services\SystemActionService;
use App\Services\TwoFactorAuthService;
use App\Services\UserService;
use Carbon\Carbon;
use Google\Service\Analytics\Goals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiController extends Controller
{


    protected $twoFactorAuthService;
    protected SystemActionService $systemActionService;


    public function __construct(TwoFactorAuthService $twoFactorAuthService, SystemActionService $systemActionService)
    {
        $this->twoFactorAuthService = $twoFactorAuthService;
        $this->systemActionService = $systemActionService;
    }

    private function storeImage($request)
    {
        return (new StorageHelper($request->user()->id, 'user'))->storeFile($request->file('image'));
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            // 'phone_number' => 'required|unique:users',
            'password' => 'required|confirmed|string|min:6',

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first(),
            ];
            return response()->json($message, 500);
        }
        if ($request->has('image')) {
            // $path = $request->file('image')->store('user');
            $path = $this->storeImage($request);
        } else {
            $path = "";
        }

        $date = Carbon::now();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'image' => $path,
            'trial_start' => Carbon::now(),
            'trial_end' => $date->addDays(14),
            // 'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
            'fcm_token' => '',

        ]);

        Mail::to($user->email)->send(new NewUserEmail($user));

        $this->systemActionService->logAction(SystemActionType::USER_REGISTERED, [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $this->systemActionService->logAction(SystemActionType::TRIAL_STARTED, [
            'user_id' => $user->id, 
            'name' => $user->name, 
            'email' => $user->email,
        ]);

        return $this->authenticate($request);
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'unique:users,email,' . Auth::user()->id,
        ]);
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first(),
            ];
            return response()->json($message, 500);
        }
        $data = $request->except('image');
        if ($request->hasfile('image')) {
            // $path = $request->file('image')->store('user');
            $path = $this->storeImage($request);
            $data['image'] = $path;
        }
        User::where('id', Auth::user()->id)->update($data);
        $newUser = User::find(Auth::user()->id);
        return response()->json([
            'success' => true,
            'data' => $newUser,
            'message' => 'Update Successfully',
        ], Response::HTTP_OK);
    }

    public function ResetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'email' => ['required'],

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first(),
            ];
            return response()->json($message, 500);
        }

        $user = User::where('email', $request->email)->first();

        if (isset($user)) {
            $user->update(['password' => bcrypt($request->password)]);

            return response()->json([
                'status' => true,
                "message" => 'Updated Successfully',
            ]);
        } else {
            return response()->json([
                'status' => true,
                "message" => 'Invalid Email ',
            ]);
        }
    }

    public function change_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'currentpassword' => ['required'],

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first(),
            ];
            return response()->json($message, 500);
        }
        if (Hash::check($request->currentpassword, Auth::user()->password)) {
            User::whereId(Auth::user()->id)->update(['password' => bcrypt($request->password)]);
            return response()->json([
                'status' => true,
                "message" => 'Updated Successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Current Password Does Not Match',
            ], 500);
        }
    }

    public function authenticate(Request $request)
    {

        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 500);
        }

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $user = Auth::user();

        // Check If account is disabled
        $activeUser = User::getActiveUser($user->id);
        if(empty($activeUser)) {
            return response()->json([
                'message' => 'Your account is deactivated',
            ], 403);
        }

        // Check if 2FA is enabled for the user
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();
        if ($twoFactorAuth && $twoFactorAuth->is_enabled) {
            // Generate and send 2FA code
            app(TwoFactorAuthService::class)->generateAndSendCode($user);

            return response()->json([
                'success' => true,
                'message' => '2FA code sent. Please verify.',
                'requires_2fa' => true,
                'user_id' => $user->id,
            ]);
        }

        // If 2FA is not enabled, proceed with normal login
        if ($request->device_type != "web") {
            $user->fcm_token = @$request->fcm_token;
            $user->save();
        } else {
            $user = Auth::user();
        }
        $trialEndDate = Carbon::parse($user->trial_end);
        if (!$trialEndDate->isFuture()) {
            $user->trial_end = true;
        } else {
            $user->trial_end = false;
        }
        $coupon = UserCoupon::with('coupon')->where('user_id', $user->id)->get();
        foreach ($coupon as $coup) {
            if (UsedCoupon::where(['user_id' => Auth::user()->id, 'coupon_id' => $coup->coupon->id])->exists()) {
                $coup->used = true;
            } else {
                $coup->used = false;
            }
        }

        $subscription = Subscription::where('user_id', $user->id)->first();
        $date = Carbon::now()->startOfDay();
        $isDailyAdded = Mood::isDailyAdded($user->id);
        $onboarding = Onboarding::where('user_id', $user->id)->get();
        $userExperience = UserExperience::where('user_id', $user->id)->first();
        $userNotificationSettings = $user->userNotificationSettings;
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

        // Transform onboarding array to [key => value] object
        $onboarding = $onboarding->mapWithKeys(function ($item) {
            return [$item['key'] => $item['value']];
        });

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
            'user_details' => $user,
            'subscription' => $subscription,
            'promocode' => $coupon,
            'is_daily_added' => $isDailyAdded,
            'onboarding' => $onboarding,
            'user_experience' => $userExperience,
            'user_notification_settings' => $userNotificationSettings,
            'two_factor' => $twoFactor,
        ]);
    }

    public function logout(Request $request)
    {
        //Request is validated, do logout
        try {

            $user = Auth::user();

            $user->fcm_token = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out',
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete_account(Request $request)
    {
        //Request is validated, do logout

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|max:50',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first(),
            ];
            return response()->json($message, 500);
        }

        try {
            $user = auth()->user();

            if (Hash::check($request->password, $user->password)) {

                Goal::where('user_id', $user->id)->delete();
                GoalTemplate::where('user_id', $user->id)->delete();
                Homework::where('user_id', $user->id)->delete();
                HomeworkModel::where('user_id', $user->id)->delete();
                HomeworkTemplate::where('user_id', $user->id)->delete();
                Mood::where('user_id', $user->id)->delete();
                Note::where('user_id', $user->id)->delete();
                Notification::where('user_id', $user->id)->delete();
                Onboarding::where('user_id', $user->id)->delete();
                Subscription::where('user_id', $user->id)->delete();
                UsedCoupon::where('user_id', $user->id)->delete();
                UserCoupon::where('user_id', $user->id)->delete();
                UserSymptom::where('user_id', $user->id)->delete();
                User::whereId($user->id)->delete();

                // Fix: Use the enum correctly
                $this->systemActionService->logAction(
                    SystemActionType::USER_ACCOUNT_DELETED,
                    [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'User has been deleted',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Password does not match',
                ], 400);
            }
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be deleted',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function forgot(Request $request)
    {

        $credentials = request()->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (isset($user)) {
            $status = Password::sendResetLink($credentials);
            if ($status == Password::RESET_LINK_SENT) {

                return [
                    'status' => true,
                    'message' => __($status),
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Server issue try after some time ',
                ];
            }
            return response()->json([
                'status' => true,
                "message" => 'Reset password link sent on your email address.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                "message" => 'Email Id is not Exist ',
            ]);
        }
    }

    public function verifyCode(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $user = User::where('email', $request->email)->first();

        $credentials = request()->validate(['email' => 'required|email']);
        $date = Carbon::now();
        $date = strtotime($date);
        $now = date("Y-m-d H:i:s", $date);

        if ($user->code_expiry < $now) {
            return response()->json([
                'status' => true,
                "message" => 'Verification Code Expired',
            ]);
        } else {
            if ($request->code == $user->verification_code) {
                // Password::sendResetLink($credentials);

                return response()->json([
                    'status' => true,
                    "message" => 'Success.',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    "message" => 'Please enter correct verification code',

                ]);
            }
        }
    }

    public function get_user(Request $request)
    {
        //$user =User::get();
        $user = JWTAuth::authenticate($request->bearerToken());
        $profile = UserService::getUserProfile($user);

        return response()->json($profile);
    }

    public function socialloginwith()
    {
        $credentials = request(['provider_id', 'password']);
        $email = request('email');
        $devices = request(['device_type', 'device_token', 'fcm_token']);
        if (!$token = JWTAuth::attempt($credentials)) {

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        User::where('email', $email)
            ->update(['provider_id' => $credentials['provider_id'], 'device_type' => $devices['device_type'], 'device_token' => $devices['device_token'], 'fcm_token' => $devices['fcm_token']]);
        return response()->json([
            'token' => $token,
            "status" => 200,
        ]);
    }

    public function socialLogin(Request $request)
    {

        //return $request;
        //dd($request->all());
        if ($user = User::where('provider_id', '=', $request->provider_id)->first()) {
            $email = $user->email;
            $name = $user->name;
            $provider_name = $user->provider_name;
            $provider_id = $request->provider_id;
            $device_type = $user->device_type;
            $device_token = $user->device_token;
            $fcm_token = $user->fcm_token;
            $mobile_number = $user->mobile_number;
        } else {

            $email = $request->email;
            $name = $request->name;
            $provider_name = $request->provider_name;
            $provider_id = $request->provider_id;
            $device_type = $request->device_type;
            $mobile_number = $request->mobile_number;
            $device_token = $request->device_token;
            $fcm_token = $request->fcm_token;
        }

        if (User::where('email', '=', $email)->count() > 0) {

            $ex = User::where('email', $email)->first();

            if ($ex->provider_name == null) {

                $email = $email;
                $password = Hash::make($request->password);

                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            } elseif (User::where('provider_id', '=', $provider_id)->count() > 0) {

                // $email = $request->email;
                $password = Hash::make($request->password);
                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            } else {
                User::where('email', $email)
                    ->update(['provider_id' => $provider_id, 'provider_name' => $provider_name]);

                $email = $email;
                $password = Hash::make($request->password);

                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            }
        } else {

            if (User::where('provider_id', '=', $provider_id)->count() > 0) {
                $email = $email;
                $password = Hash::make($request->password);
                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'mobile_number' => $request->mobile_number,
                    'provider_id' => $request->provider_id,
                    'provider_name' => $request->provider_name,
                    'password' => Hash::make($request->password),
                    'device_type' => $request->device_type,
                    'device_token' => $request->device_token,
                    'fcm_token' => $request->fcm_token,
                ]);
                $email = $email;
                $password = $request->password;

                return $this->authenticate($request);
            }
        }
    }
    public function destroy()
    {
        Goal::where('user_id', Auth::user()->id)->delete();
        Note::where('user_id', Auth::user()->id)->delete();
        UsedCoupon::where('user_id', Auth::user()->id)->delete();
        HomeworkModel::where('user_id', Auth::user()->id)->delete();
        Notification::where('user_id', Auth::user()->id)->delete();
        User::whereId(Auth::user()->id)->delete();
        return response()->json([
            "status" => 200,
            "message" => "Deleted Successfully",
        ]);
    }
    public function applyCoupon($couponid)
    {

        $coupon = Coupon::find($couponid);
        if (UsedCoupon::where(['user_id' => Auth::user()->id, 'coupon_id' => $couponid])->exists()) {
            $message = 'You have already Used';
        } else {
            $user = User::find(Auth::user()->id);
            $user->trial_end = Carbon::now()->addDays($coupon->days);
            $user->save();
            UsedCoupon::create(['user_id' => Auth::user()->id, 'coupon_id' => $couponid]);
            $message = 'Coupon Applied';
        }

        return \response()->json(['status' => true, 'message' => $message]);
    }

}
