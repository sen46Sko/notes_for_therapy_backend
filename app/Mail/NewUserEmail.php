<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserEmail extends Mailable
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
        return $this->subject('Welcome to Notes For Therapy! Start enhancing your therapy journey today.')->view('emails.newUserEmail',compact('user'));
    }
}
