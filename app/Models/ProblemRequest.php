<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemRequest extends Model
{
    use HasFactory;

    protected $fillable = ['text', 'problem_id', 'problem_description', 'email'];

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }
}
