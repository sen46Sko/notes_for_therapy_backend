<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onboarding extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'key',
        'value',
        'user_id',
    ];

    protected $casts = [
        'value' => 'boolean',
    ];
}
