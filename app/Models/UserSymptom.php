<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSymptom extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'symptom_id', 'user_id', 'intensity', 'note'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function symptom()
    {
        return $this->belongsTo(Symptom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
