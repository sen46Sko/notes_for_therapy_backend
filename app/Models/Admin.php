<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'deactivate_to',
        'role',
        'avatar',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor_email',
        'password_changed_at',
        'last_activity_at',
    ];

    protected $appends = [];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deactivate_to' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'password_changed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function rights()
    {
        return $this->hasOne(AdminRights::class);
    }

    public function registerLink()
    {
        return $this->hasOne(AdminRegisterLink::class);
    }

    public function isDeactivated()
    {
        return $this->deactivate_to && now()->lt($this->deactivate_to);
    }
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
    public function getAvatarAttribute($value)
        {
            return $value ? asset('storage/' . $value) : null;
        }

    public function setAvatarAttribute($value)
    {
        if ($value && strpos($value, 'storage/') !== false) {
            $parts = explode('storage/', $value);
            if (count($parts) > 1) {
                $this->attributes['avatar'] = $parts[1];
                return;
            }
        }

        $this->attributes['avatar'] = $value;
    }

    public function hasTwoFactorEnabled()
    {
        return $this->two_factor_enabled;
    }

    public function twoFactorCodeIsValid($code)
    {
        return $this->two_factor_code === $code &&
               $this->two_factor_expires_at &&
               now()->lte($this->two_factor_expires_at);
    }

    public function updatePasswordChangedAt()
        {
            $this->update([
                'password_changed_at' => now()
            ]);
        }
    public function devices()
    {
        return $this->hasMany(AdminDevice::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(AdminActivityLog::class);
    }

}
