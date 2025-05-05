<?php

namespace App\Console\Commands;

use App\Models\SecuritySettings;
use Illuminate\Console\Command;

class TestSessionTimeout extends Command
{
    protected $signature = 'test:session-timeout';
    protected $description = 'Test session timeout by setting it to 1 minute';

    public function handle()
    {
        $settings = SecuritySettings::first();

        if ($settings) {

            $settings->update([
                'session_timeout_enabled' => true,
                'session_timeout_duration' => '1 minute'
            ]);

            $this->info('Session timeout set to 1 minute for testing');
        }

        return 0;
    }
}