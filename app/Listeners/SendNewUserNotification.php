<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\AdminNotificationsEventService;

class SendNewUserNotification implements ShouldQueue
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
    public function handle(Registered $event): void
    {
        $this->notificationsService->handleNewUserRegistration($event->user);
    }
}