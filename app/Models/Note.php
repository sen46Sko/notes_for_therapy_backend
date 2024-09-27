<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'question_id', 'note', 'user_id'];

    public function question()
    {
        return $this->belongsTo(NoteQuestion::class, 'question_id');
    }
}
