<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_subscription_id',
        'provider_purchase_token',
        'status',
        'trial_start',
        'trial_end',
        'expiration_date',
        'coupon_code'
    ];

    protected $dates = [
        'trial_start',
        'trial_end',
        'expiration_date',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'active' &&
               ($this->expiration_date === null ||
                Carbon::parse($this->expiration_date)->isFuture());
    }

    public function isInTrial()
    {
        if (!$this->trial_start || !$this->trial_end) {
            return false;
        }

        $now = Carbon::now();
        return $now->between(
            Carbon::parse($this->trial_start),
            Carbon::parse($this->trial_end)
        );
    }
}
