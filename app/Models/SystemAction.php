<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAction extends Model
{
    protected $fillable = ['action_type', 'payload'];

    protected $casts = [
        'payload' => 'array',
        // Make sure there's no cast for action_type
    ];
}
