<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthStats extends Model
{
    use HasFactory;

    protected $table = "month_stats";

    protected $fillable = [
        'date', 
        'total_users',
        'subscription_counter',
        'trial_counter',
        'cancel_counter',
        'monthly_plan',
        'yearly_plan',
        'signups',
        'delete_account_counter',
        'resolved_tickets',
        'ticket_created'
    ];

    public function incrementCounter(string $columnName): int {
        $latestEntry = self::query()->orderBy('date', 'desc')->first();

        if(!$latestEntry) {
            return 0;
        }

        return $latestEntry->increment($columnName);
    }
}
