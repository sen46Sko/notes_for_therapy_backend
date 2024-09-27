<?php
// app/Models/Mood.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Mood extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'type',
        'mood_relation_id',
        'note',
        'user_id', // Add this line
    ];

    public function moodRelation()
    {
        return $this->belongsTo(MoodRelation::class, 'mood_relation_id');
    }

    public function moodFeelings()
    {
        return $this->belongsToMany(MoodFeeling::class, 'moods_mood_feelings', 'mood_id', 'mood_feeling_id');
    }

    public function user() // Add this method
    {
        return $this->belongsTo(User::class);
    }

    public static function isDailyAdded($user_id)
    {
        $date = Carbon::now()->startOfDay();
        return Mood::where('user_id', $user_id)
            ->where('type', 'daily')
            ->where('created_at', '>=', $date)
            ->where('created_at', '<', $date->copy()->addDay())
            ->exists();
    }

}
