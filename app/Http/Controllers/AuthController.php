<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Mail\AuthAlertEmail;
use App\Mail\OtpEmail;
use App\Models\Subscription;
use App\Models\UsedCoupon;
use App\Models\User;
use App\Models\Onboarding;
use App\Models\TwoFactorAuth;
use App\Models\UserCoupon;
use App\Models\UserExperience;
use App\Services\GoogleAuthService;
use App\Services\SystemActionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $googleAuth;
    protected SystemActionService $systemActionService;

    public function __construct(GoogleAuthService $googleAuth, SystemActionService $systemActionService)
    {
        $this->googleAuth = $googleAuth;
        $this->systemActionService = $systemActionService;
    }
    private function generateOtpForUser(User $user)
    {
        // Generate OTP (One-Time Password)
        $otp_allowed_symbols = "0123456789";
        $otp_length = 6; // Define the length of the OTP

        $otp_code = '';
        $symbols_length = strlen($otp_allowed_symbols);

        // Generate random OTP code
        for ($i = 0; $i < $otp_length; $i++) {
            $otp_code .= $otp_allowed_symbols[rand(0, $symbols_length - 1)];
        }

        // Save OTP code to the user record (you may want to store this in a database or cache)
        $user->otp_code = $otp_code;

        $otp_expires_at = Carbon::now()->addMinutes(10);
        $user->otp_expires = $otp_expires_at;

        return $user;
    }

    //
    public function requestPasswordChange(Request $request)
    {
        // Extract email from query params
        $request_email = $request->query('email');
        $user = User::where('email', $request_email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 400);
        }

        $user = $this->generateOtpForUser($user);
        $user->save();

        Mail::to($user->email)->send(new OtpEmail($user, $user->otp_code));

        return response()->json([
            'success' => true,
            'message' => 'OTP generated and sent successfully'
        ]);

    }

    public function requestEmailChange(Request $request) {
        $newEmail = $request->query('email');

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 400);
        }

        // Check if new email is not being used by another user
        if (User::where('email', $newEmail)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already in use'
            ], 400);
        }

        $user = $this->generateOtpForUser($user);
        $user->save();

        Mail::to($newEmail)->send(new OtpEmail($user, $user->otp_code));

        return response()->json([
            'success' => true,
            'message' => 'OTP generated and sent successfully'
        ]);
    }

    public function checkOtp(Request $request)
    {
        $request_otp = $request->query('otp');
        $user = User::where(
            'email',
            $request->query('email')
        )->where('otp_code', $request_otp)->first();
        // Check if otp is not expired
        if (!$user || $user->otp_expires < Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP is valid'
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'email' => ['required'],
            'otp_code' => ['required']
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message, 400);
        }

        $user = User::where('email', $request->email)->where('otp_code', $request->otp_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or OTP'
            ], 400);
        }

        if ($user->otp_expires < Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    public function changePasswordAuthorized(Request $request) {
        $validator = Validator::make($request->all(), [
            'currentPassword' => ['required'],
            'password' => ['required'],
            'confirmPassword' => ['required']
        ]);

        // Check if user is not created using Google OAuth
        $user = auth()->user();

        if ($user->is_google_signup == 1) {
            return response()->json([
                'success' => false,
                'message' => 'User created using Google OAuth. Please use Google OAuth to change password.'
            ], 400);
        }

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
            return response()->json($message, 400);
        }

        if ($request->password != $request->confirmPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Passwords do not match'
            ], 400);
        }

        if (!password_verify($request->currentPassword, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
            'fcm_token' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    public function changeEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'otp_code' => ['required']
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message, 400);
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or OTP'
            ], 400);
        }

        if ($user->otp_expires < Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        $user->update([
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email changed successfully'
        ]);
    }

    public function googleOAuth(Request $request)
    {
        try {
            // Retrieve user data from Google
            // $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->idToken);
            $googleUser = $this->googleAuth->verifyIdToken($request->idToken);

            if (is_null($googleUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google ID token'
                ], 400);
            }

            $user = User::where('email', $googleUser['email'])->first();

                    // Check If account is disabled
            $activeUser = User::getActiveUser($user->id);
            if(empty($activeUser)) {
                return response()->json([
                    'message' => 'Your account is deactivated',
                ], 403);
            }

            $is_signup = false;

            if (is_null($user)) {
                $date = Carbon::now();
                $user = User::create([
                    // Set to givenName or name
                    'name' => $googleUser['givenName'] ?? $googleUser['name'],
                    'email' => $googleUser['email'],
                    'image' => $googleUser['picture'],
                    'trial_start' => Carbon::now(),
                    'trial_end' => $date->addDays(14),
                    'fcm_token' => '',
                    'password' => ""
                    'country_code' => $request->country_code

                ]);
                $user->is_google_signup = true;
                $user->save();
                $is_signup = true;

                $this->systemActionService->logAction(SystemActionType::USER_REGISTERED_VIA_GOOGLE, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'fcm_token' => $user->fcm_token,
                ]);
            }

            if ($user->is_google_signup == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'An account with this email already exists. Please use your email and password to log in instead of Google Sign-In.'
                ], 400);
            }
            // Update user's FCM token
            $user->fcm_token = $request->fcm_token;
            $user->save();

            if (!$userToken = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'Unauthorized'], 401);
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

            $userExperience = UserExperience::where('user_id', $user->id)->first();
            $onboarding = Onboarding::where('user_id', $user->id)->get();

            // Transform onboarding array to [key => value] object
            $onboarding = $onboarding->mapWithKeys(function ($item) {
                return [$item['key'] => $item['value']];
            });

            $userNotificationSettings = $user->userNotificationSettings;
            $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

            $this->systemActionService->logAction(SystemActionType::USER_LOGGED_IN_VIA_GOOGLE, [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

            //Token created, return with success response and jwt token
            return response()->json([
                'success' => true,
                'token' => $userToken,
                'user_details' => $user,
                'subscription' => $subscription,
                'promocode' => $coupon,
                'just_signed_up' => $is_signup,
                'onboarding' => $onboarding,
                'user_experience' => $userExperience,
                'user_notification_settings' => $userNotificationSettings,
                'two_factor' => $twoFactor
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication failed: ' . $e->getMessage()], 401);
        }
    }

    public function unsuccessfulAuthAlert(Request $request) {
        // Send email authAlert to currently logged in user
        $user =  Auth::user();
        $deviceInfo = $request->device_info;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 400);
        }

        Mail::to($user->email)->send(new AuthAlertEmail($user, $deviceInfo));

        return response()->json([
            'success' => true,
            'message' => 'Auth alert sent successfully'
        ]);
    }
}
