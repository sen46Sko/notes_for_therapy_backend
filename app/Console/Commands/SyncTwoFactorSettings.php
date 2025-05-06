<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\SecuritySettings;
use Illuminate\Console\Command;

class SyncTwoFactorSettings extends Command
{
    protected $signature = 'sync:2fa';
    protected $description = 'Sync 2FA settings from global security settings to all admins';

    public function handle()
    {
        $settings = SecuritySettings::first();

        if (!$settings) {
            $this->error('Security settings not found');
            return 1;
        }

        $twoFactorEnabled = $settings->two_factor_enabled;

        Admin::query()->update([
            'two_factor_enabled' => $twoFactorEnabled
        ]);

        $this->info(sprintf(
            '2FA has been %s for all admins',
            $twoFactorEnabled ? 'enabled' : 'disabled'
        ));

        return 0;
    }
}