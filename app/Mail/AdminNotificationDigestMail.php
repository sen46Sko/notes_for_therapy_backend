<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;

class AdminNotificationDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $notifications;
    public $frequency;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $admin, Collection $notifications, $frequency)
    {
        $this->admin = $admin;
        $this->notifications = $notifications;
        $this->frequency = $frequency;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $frequencyName = ucfirst($this->frequency);

        return $this->subject("{$frequencyName} Notification Digest")
            ->view('emails.admin-notification-digest')
            ->with([
                'admin' => $this->admin,
                'notifications' => $this->notifications,
                'frequency' => $this->frequency
            ]);
    }
}