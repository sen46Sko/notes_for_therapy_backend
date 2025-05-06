<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $code;

    public function __construct(Admin $admin, string $code)
    {
        $this->admin = $admin;
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Your 2FA Verification Code')
                    ->view('emails.admin-otp');
    }
}