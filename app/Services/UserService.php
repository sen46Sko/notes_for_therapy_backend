<?php

namespace App\Services;

use App\Models\Mood;
use App\Models\Onboarding;
use App\Models\Subscription;
use App\Models\TwoFactorAuth;
use App\Models\UsedCoupon;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\UserExperience;
use Illuminate\Support\Facades\Auth;

class UserService
{
  public static function getUserProfile(User $user) {
    $subscription = Subscription::where('user_id', $user->id)->first();
    $coupon = UserCoupon::with('coupon')->where('user_id', $user->id)->get();
    foreach ($coupon as $coup) {
        if (UsedCoupon::where(['user_id' => Auth::user()->id, 'coupon_id' => $coup->coupon->id])->exists()) {
            $coup->used = true;
        } else {
            $coup->used = false;
        }
    }
    $isDailyAdded = Mood::isDailyAdded($user->id);
    $onboarding = Onboarding::where('user_id', $user->id)->get();
    $userNotificationSettings = $user->userNotificationSettings;
    $userExperience = UserExperience::where('user_id', $user->id)->first();
    $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

    // Transform onboarding array to [key => value] object
    $onboarding = $onboarding->mapWithKeys(function ($item) {
        return [$item['key'] => $item['value']];
    });


    return [
        "status" => true,
        'user' => $user,
        'subscription' => $subscription,
        'promocode' => $coupon,
        'is_daily_added' => $isDailyAdded,
        'onboarding' => $onboarding,
        'user_experience' => $userExperience,
        'user_notification_settings' => $userNotificationSettings,
        'two_factor' => $twoFactor,
    ];
  }
}
