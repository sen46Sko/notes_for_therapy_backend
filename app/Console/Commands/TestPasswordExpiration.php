<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\SecuritySettings;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestPasswordExpiration extends Command
{
    protected $signature = 'test:password-expiration {email}';
    protected $description = 'Test password expiration for an admin';

    public function handle()
    {
        $email = $this->argument('email');
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            $this->error("Admin with email {$email} not found");
            return 1;
        }

        $settings = SecuritySettings::first();
        if ($settings) {
            $settings->update([
                'periodic_password_changes_enabled' => true,
                'password_change_period' => '1 month'
            ]);
            $this->info("Password policy enabled: change required every 1 month");
        }

        $admin->update([
            'password_changed_at' => Carbon::now()->subMonths(2)
        ]);

        $this->info("Set last password change for {$email} to 2 months ago");
        $this->info("Password should be expired now. Try logging in.");

        return 0;
    }
}