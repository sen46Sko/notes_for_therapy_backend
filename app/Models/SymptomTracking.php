<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SymptomTracking extends Model
{
    use HasFactory;

    protected $guarded =[];
    public function symptom(){
        return $this->belongsTo(Symptom::class,'symptom_id');
    }

}
