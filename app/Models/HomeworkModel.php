<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkModel extends Model
{
    use HasFactory;

    protected $guarded=[];

    protected $fillable = [
        'user_id',
        'short_description',
        'file',
        'description',
        'thoughts',
        'notification_message'
    ];
}
