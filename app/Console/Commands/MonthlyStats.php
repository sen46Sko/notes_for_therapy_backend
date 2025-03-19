<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Models\MonthStats;

class MonthlyStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:monthly_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = Carbon::now()->startOfMonth();

        $totalUsers = User::count();

        $exists = MonthStats::whereYear('date', $date->year)->whereMonth('date', $date->month)->exists(); 

        if(!$exists) {
            MonthStats::create([
                'date' => $date,
                'total_users' => $totalUsers,
            ]);
        }

        return 0;
    }
}
