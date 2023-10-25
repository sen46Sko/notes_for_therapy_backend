<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    
    protected $fillable=[
        'user_id',
        'subscription_id',
        'subscription_start',
        'subscription_end',
        'customer',
        'plan_id',
        'plan_amount',
        'currency',
        'interval',
        'payment_status',
        'comments',
        'type'

    ];
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
