<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'deadline', 'completed_at', 'remind_at', 'notification_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(HomeworkTemplate::class, 'title', 'title');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
