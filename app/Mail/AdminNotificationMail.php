<?php

namespace App\Mail;

use App\Models\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;

    /**
     * Create a new message instance.
     */
    public function __construct(AdminNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Admin Notification: {$this->notification->title}")
            ->view('emails.admin-notification')
            ->with([
                'notification' => $this->notification,
            ]);
    }
}