<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AuthAlertEmail extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;
    private Carbon $attemptTime;
    private String $deviceInfo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, String $deviceInfo)
    {
        $this->user =$user;
        $this->attemptTime = Carbon::now();
        $this->deviceInfo = $deviceInfo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user =$this->user;
        $attemptTime = $this->attemptTime;
        $deviceInfo = $this->deviceInfo;
        return $this->subject('Notes For Therapy: Security Alert')->view('emails.authAlert',compact('user', 'attemptTime', 'deviceInfo'));
    }
}
