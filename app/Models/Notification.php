<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Notification extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'show_at',
        'status',
        'type',
        'title',
        'description',
        'repeat',
        'user_id',
        'entity_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function routeNotificationForFcm() {
        return $this->user->fcm_token;
        // TODO: replace with actual fcm_token;
        // return 'cTwW9F5o90XKr4_XazRCAB:APA91bE--S6kalDIeCxJUeeFebwCY6Mh0mPNoJzhdSRZO0Q7NdgrokCtSo-sxw7HadLSQfBrtJRV_TDKNQowcikNDxscLWR_zay0F3kEWMdBNquX9fOQgDIdkzBG4bcpeyZCYoUQnWNd';
    }
}
