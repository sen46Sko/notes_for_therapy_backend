<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearStats extends Model
{
    use HasFactory;

    protected $table = "year_stats";

    protected $fillable = [
        'year', 
        'total_users',
        'subscription_counter',
        'trial_counter',
        'cancel_counter',
        'monthly_plan',
        'yearly_plan',
        'signups',
        'delete_account_counter',
        'resolved_tickets',
        'ticket_create'
    ];

    public function incrementCounter(string $columnName): int {
        $latestEntry = self::query()->orderBy('date', 'desc')->first();

        if(!$latestEntry) {
            return 0;
        }

        return $latestEntry->increment($columnName);
    }
}
