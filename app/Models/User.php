<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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

        // New columns
        'gender',
        'age',

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
    ];
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

    public function routeNotificationForFcm() {
        return $this->fcm_token;
        // TODO: replace with actual fcm_token;
        // return 'cTwW9F5o90XKr4_XazRCAB:APA91bE--S6kalDIeCxJUeeFebwCY6Mh0mPNoJzhdSRZO0Q7NdgrokCtSo-sxw7HadLSQfBrtJRV_TDKNQowcikNDxscLWR_zay0F3kEWMdBNquX9fOQgDIdkzBG4bcpeyZCYoUQnWNd';
    }
}
