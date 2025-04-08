<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'age',
        'email',
        'password',
        'image',
        'mobile_number',
        'provider_id',
        'code_expiry',
        'verification_code',
        // 'phone_number',
        'device_type',
        'device_token',
        'fcm_token',
        'image',
        'paystatus',
        'subscription_status',
        'user',
        'verify_status',
        'trial_start',
        'trial_end',

        // OTP
        'otp_code',
        'otp_expires',
        'uuid',

        // New columns
        'gender',
        'age',
        'deactivate_to',
        'account_status'

    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'deactivated_to' => 'datetime'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['subscription_status'];

    /**
     * Ensure we always load the subscription relation
     */
    protected $with = ['subscription'];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });

        static::updating(function ($user) {
            if ($user->isDirty('email') && $user->is_apple_signup) {
                throw new \Exception('Email cannot be changed for Apple Sign-In accounts');
            }
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function note(){
        return $this->hasMany(Note::class);
    }

    public function userSymptoms()
    {
        return $this->hasMany(UserSymptom::class);
    }


    public function userExperience()
    {
        return $this->hasOne(UserExperience::class);
    }

    public function userNotificationSettings()
    {
        return $this->hasOne(UserNotificationSetting::class)->withDefault(function ($userNotificationSetting, $user) {
            $defaults = [
                'show_notifications' => true,
                'sound' => true,
                'preview' => true,
                'mail' => true,
                'marketing_ads' => true,
                'reminders' => true,
                'mood' => true,
                'notes' => true,
                'symptoms' => true,
                'goals' => true,
                'homework' => true,
                'user_id' => $user->id,
            ];

            $userNotificationSetting->fill($defaults);
            $userNotificationSetting->save();

            return $userNotificationSetting;
        });
    }

    public function routeNotificationForFcm() {
        return $this->fcm_token;
        // TODO: replace with actual fcm_token;
        // return 'cTwW9F5o90XKr4_XazRCAB:APA91bE--S6kalDIeCxJUeeFebwCY6Mh0mPNoJzhdSRZO0Q7NdgrokCtSo-sxw7HadLSQfBrtJRV_TDKNQowcikNDxscLWR_zay0F3kEWMdBNquX9fOQgDIdkzBG4bcpeyZCYoUQnWNd';
    }



    /**
     * Get user's subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscription if exists, otherwise get the most recent one
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)
            ->orderByRaw("CASE
                WHEN status = 'active' THEN 1
                WHEN status = 'trial' THEN 2
                ELSE 3
            END")
            ->latest();
    }

    /**
     * Get the subscription status
     */
    public function getSubscriptionStatusAttribute()
    {
        if (!$this->relationLoaded('subscription')) {
            $this->load('subscription');
        }

        return $this->subscription ? $this->subscription->status : 'inactive';
    }

    public function isDeactivated()
    {
        return $this->deactivate_to && now()->lt($this->deactivate_to);
    }

    public static function getActiveUser(string $user_id) {
        $user = User::where('id', $user_id)->first();
        
        if (!$user || $user->isDeactivated()) {
            return null;
        }
        
        $user->account_status = 'active';
        $user->save();
        
        return $user;
    }
}
