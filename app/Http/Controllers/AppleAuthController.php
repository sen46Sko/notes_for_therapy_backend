<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use App\Models\UserExperience;
use App\Models\Onboarding;
use App\Models\UserCoupon;
use App\Models\UsedCoupon;
use App\Models\TwoFactorAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AppleAuthService;

class AppleAuthController extends Controller
{
    protected $appleAuth;

    public function __construct(AppleAuthService $appleAuth)
    {
        $this->appleAuth = $appleAuth;
    }

    public function signIn(Request $request)
    {
        try {
            $appleUser = $this->appleAuth->verifyToken($request->jwt);

            if (is_null($appleUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Apple ID token'
                ], 400);
            }

            $user = User::where('email', $appleUser['email'])->first();

            if (is_null($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Please sign up first.'
                ], 404);
            }

            if ($user->is_google_signup == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'An account with this email already exists. Please use your email and password to log in instead of Apple Sign-In.'
                ], 400);
            }

            // Update FCM token
            $user->fcm_token = $request->fcm;
            $user->save();

            return $this->generateUserResponse($user);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication failed: ' . $e->getMessage()], 401);
        }
    }

    public function signUp(Request $request)
    {
        try {
            $appleUser = $this->appleAuth->verifyToken($request->jwt);

            if (is_null($appleUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Apple ID token'
                ], 400);
            }

            $existingUser = User::where('email', $appleUser['email'])->first();

            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already exists'
                ], 400);
            }

            $date = Carbon::now();
            $user = User::create([
                'name' => $request->name,
                'email' => $appleUser['email'],
                'image' => null,
                'trial_start' => Carbon::now(),
                'trial_end' => $date->addDays(14),
                'subscription_status' => 0,
                'fcm_token' => $request->fcm,
                'password' => "",
            ]);
            $user->is_google_signup = true;
            $user->save();

            return $this->generateUserResponse($user, true);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed: ' . $e->getMessage()], 401);
        }
    }

    protected function generateUserResponse($user, $is_signup = false)
    {
        if (!$userToken = JWTAuth::fromUser($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $trialEndDate = Carbon::parse($user->trial_end);
        $user->trial_end = !$trialEndDate->isFuture();

        $coupon = UserCoupon::with('coupon')->where('user_id', $user->id)->get();
        foreach ($coupon as $coup) {
            $coup->used = UsedCoupon::where([
                'user_id' => $user->id,
                'coupon_id' => $coup->coupon->id
            ])->exists();
        }

        $subscription = Subscription::where('user_id', $user->id)->first();
        $userExperience = UserExperience::where('user_id', $user->id)->first();
        $onboarding = Onboarding::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item['key'] => $item['value']];
            });
        $userNotificationSettings = $user->userNotificationSettings;
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

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
    }
}
