<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registrationLink;

    public function __construct(string $registrationLink)
    {
        $this->registrationLink = $registrationLink;
    }

        public function build()
    {

        \Illuminate\Support\Facades\Log::info('AdminRegistrationMail link: ' . $this->registrationLink);

        return $this
            ->from('no-reply@notesfortherapy.com', 'Notes for Therapy')
            ->subject('Admin Registration')
            ->html('
                <h2>Admin Registration</h2>
                <p>You have been invited to register as an admin.</p>
                <p>Please complete your registration by clicking the link below:</p>
                <p><a href="'.$this->registrationLink.'">Complete Registration</a></p>
                <p>Or copy and paste this URL into your browser:</p>
                <p>'.$this->registrationLink.'</p>
                <p>If you did not request this registration, please ignore this email.</p>
            ');
    }
}