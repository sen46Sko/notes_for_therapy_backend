<?php
// app/Models/MoodFeeling.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoodFeeling extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
    ];
}
