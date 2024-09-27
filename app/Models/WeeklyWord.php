<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyWord extends Model
{
    use HasFactory;

    protected $fillable = ['rating', 'word', 'user_id'];
}
