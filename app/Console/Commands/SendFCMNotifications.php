<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\TestNotification;

class SendFCMNotifications extends Command
{
    protected $signature = 'send:fcm-notifications';
    protected $description = 'Send FCM notifications to users';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // $users = User::all(); // Adjust the query to fetch users with FCM tokens
        $user = User::where('fcm_token', '!=', null)->first();
        $user->notify(new TestNotification('Title', 'Body'));
        $this->info('FCM notifications sent successfully.');
    }
}
