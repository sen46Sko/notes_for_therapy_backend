<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'device_type',
        'device_name',
        'ip_address',
        'user_agent',
        'last_active_at',
        'is_active',
        'is_blocked',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}