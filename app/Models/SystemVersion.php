<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'release_date',
        'git_commit',
        'description',
        'changelog',
    ];

    protected $casts = [
        'release_date' => 'datetime',
    ];
}