<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'notification_message',
        'remind_at',
        'repeat',
        'user_id',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'repeat' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
