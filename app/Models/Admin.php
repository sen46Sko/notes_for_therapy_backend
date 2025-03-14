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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deactivate_to' => 'datetime',
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
}
