<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user =$user;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user =$this->user;
        if ($user->otp_code) {
            return $this->subject('Notes For Therapy: OTP Code')->view('emails.otpEmail',compact('user'));
        }
    }
}
