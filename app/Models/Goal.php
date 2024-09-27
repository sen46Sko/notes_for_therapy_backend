<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\User;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'note',
        'completed_at',
        'notification_message',
        'remind_at',
        'repeat',
        'user_id',
        'notification_id',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'remind_at' => 'datetime',
        'repeat' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
