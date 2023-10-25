<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalTracking extends Model
{
    use HasFactory;

    protected $guarded =[];
    public function goal (){
        return $this->belongsTo(Goal::class,'goal_id');
    }

}