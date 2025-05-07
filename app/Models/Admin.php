<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

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
   public function getAvatarAttribute($value)
   {
       if (!$value) {
           return null;
       }

       if (filter_var($value, FILTER_VALIDATE_URL) || strpos($value, 'http') === 0) {
           return $value;
       }

       if (config('filesystems.default') === 's3') {
           return Storage::disk('s3')->url($value);
       }

       return asset('storage/' . $value);
   }

   public function setAvatarAttribute($value)
   {
       if ($value && (strpos($value, 'http') === 0 || strpos($value, '//') === 0)) {
           $baseUrl = env('AWS_URL');
           if ($baseUrl && strpos($value, $baseUrl) === 0) {
               $this->attributes['avatar'] = str_replace($baseUrl, '', $value);
               return;
           }

           $storageUrl = asset('storage/');
           if (strpos($value, $storageUrl) === 0) {
               $this->attributes['avatar'] = str_replace($storageUrl . '/', '', $value);
               return;
           }

           $this->attributes['avatar'] = $value;
           return;
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
