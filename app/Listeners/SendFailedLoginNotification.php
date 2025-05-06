<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\AdminNotificationsEventService;

class SendFailedLoginNotification implements ShouldQueue
{
    protected $notificationsService;

    /**
     * Create the event listener.
     */
    public function __construct(AdminNotificationsEventService $notificationsService)
    {
        $this->notificationsService = $notificationsService;
    }

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';
        $ipAddress = request()->ip() ?? 'unknown';

        $this->notificationsService->handleFailedLogin($email, $ipAddress);
    }
}