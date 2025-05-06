<?php

namespace App\Services;

use App\Mail\AdminOtpEmail;
use App\Models\Admin;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AdminTwoFactorService
{
    public function enableTwoFactor(Admin $admin): void
    {
        $admin->update([
            'two_factor_enabled' => true,
            'two_factor_email' => $admin->email,
        ]);
    }

    public function disableTwoFactor(Admin $admin): void
    {
        $admin->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_email' => null,
        ]);
    }

    public function generateAndSendCode(Admin $admin): void
    {
        $code = $this->generateVerificationCode();

        $admin->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $this->sendVerificationCode($admin, $code);
    }

    public function verifyCode(Admin $admin, string $code): bool
    {
        if (!$admin->twoFactorCodeIsValid($code)) {
            return false;
        }

        $admin->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        return true;
    }

    private function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendVerificationCode(Admin $admin, string $code): void
    {
        Mail::to($admin->email)->send(new AdminOtpEmail($admin, $code));
    }
}