<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OtpEmail extends Mailable
{
    use Queueable, SerializesModels;


    public $user;
    public $otp_code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $otp_code)
    {
        $this->user =$user;
        $this->otp_code = $otp_code;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user =$this->user;
        $otp_code = $this->otp_code;
        if ($user->otp_code) {
            // Log info
            Log::info('User Email: '.$user->email);
            Log::info('OTP Code: '.$user->otp_code);
            return $this->subject('Notes For Therapy: OTP Code')->view('emails.otpEmail',compact('user', 'otp_code'));
        }
    }
}
