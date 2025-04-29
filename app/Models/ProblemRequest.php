<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'problem_id',
        'problem_description',
        'email',
        'status',
        'note',
        'assign_to',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }

    public function logs()
    {
        return $this->hasMany(ProblemLog::class, 'ticket_id');
    }

    public function messages()
    {
        return $this->hasMany(ProblemMessage::class, 'ticket_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'assign_to');
    }
}
