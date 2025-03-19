<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Models\YearStats;

class YearlyStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:yearly_stats';

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
        $year = Carbon::now()->year;

        $totalUsers = User::count();

        $exists = YearStats::where('year', $year)->exists();

        if(!$exists) {
            YearStats::create([
                'year' => $year,
                'total_users' => $totalUsers,
            ]);
        }

        return 0;
    }
}
