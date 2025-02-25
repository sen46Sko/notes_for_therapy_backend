<?php

namespace App\Models;

use App\Enums\SystemActionType;
use Illuminate\Database\Eloquent\Model;

class SystemAction extends Model
{
    protected $fillable = [
        'action_type',
        'payload'
    ];

    protected $casts = [
        'action_type' => SystemActionType::class,
        'payload' => 'array'
    ];
}
