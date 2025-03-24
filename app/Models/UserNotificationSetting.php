<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'show_notifications',
        'sound',
        'preview',
        'mail',
        'marketing_ads',
        'reminders',
        'mood',
        'notes',
        'symptoms',
        'goals',
        'homework',
        'user_id',
    ];

    protected $casts = [
        'show_notifications' => 'boolean',
        'sound' => 'boolean',
        'preview' => 'boolean',
        'mail' => 'boolean',
        'marketing_ads' => 'boolean',
        'reminders' => 'boolean',
        'mood' => 'boolean',
        'notes' => 'boolean',
        'symptoms' => 'boolean',
        'goals' => 'boolean',
        'homework' => 'boolean',
    ];
}
