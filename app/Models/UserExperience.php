<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'has_add_goal',
        'has_add_homework',
        'has_add_mood',
        'has_add_symptom',
    ];

    protected $casts = [
        'has_add_goal' => 'boolean',
        'has_add_homework' => 'boolean',
        'has_add_mood' => 'boolean',
        'has_add_symptom' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
