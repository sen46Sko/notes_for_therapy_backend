<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function goal(){
        return $this->belongsTo(Goal::class);
    }
    public function tracking(){
        return $this->hasOne(Tracking::class,'note_id');
    }

}
