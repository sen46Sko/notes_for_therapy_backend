<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
            Registered::class => [
                // Existing listeners...
                // Add our notification listener
                \App\Listeners\SendNewUserNotification::class,
            ],
            Failed::class => [
                // Existing listeners...
                // Add our failed login notification listener
                \App\Listeners\SendFailedLoginNotification::class,
            ],
            // You can add other events...
        ];


    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
