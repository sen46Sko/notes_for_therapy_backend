<?php

namespace App\Services;

use App\Mail\OtpEmail;
use App\Models\TwoFactorAuth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCode;
use Carbon\Carbon;

class TwoFactorAuthService
{
    public function initializeTwoFactor(User $user, string $type, string $value): void
    {
        if ($type !== 'email') {
            throw new \Exception('Only email-based 2FA is supported at the moment.');
        }

        $verificationCode = $this->generateVerificationCode();

        TwoFactorAuth::updateOrCreate(
            ['user_id' => $user->id],
            [
                'type' => $type,
                'value' => $value,
                'verification_code' => $verificationCode,
                'code_expires_at' => Carbon::now()->addMinutes(10),
                'is_enabled' => false,
            ]
        );

        $this->sendVerificationCode($user, $value, $verificationCode);
    }

    public function verifyTwoFactor(User $user, string $code): bool
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth) {
            throw new \Exception('2FA not initialized for this user');
        }

        if ($twoFactorAuth->verification_code !== $code) {
            throw new \Exception('Invalid verification code');
        }

        if (Carbon::now()->gt($twoFactorAuth->code_expires_at)) {
            throw new \Exception('Verification code has expired');
        }

        $twoFactorAuth->update([
            'is_enabled' => true,
            'verification_code' => null,
            'code_expires_at' => null,
        ]);

        return true;
    }

    public function generateAndSendCode(User $user): void
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth || !$twoFactorAuth->is_enabled) {
            throw new \Exception('2FA is not enabled for this user');
        }

        $verificationCode = $this->generateVerificationCode();

        $twoFactorAuth->update([
            'verification_code' => $verificationCode,
            'code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $this->sendVerificationCode($user, $twoFactorAuth->value, $verificationCode);
    }

    public function verifyCode(User $user, string $code): bool
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth) {
            throw new \Exception('2FA not initialized for this user');
        }

        if ($twoFactorAuth->verification_code !== $code) {
            throw new \Exception('Invalid verification code');
        }

        if (Carbon::now()->gt($twoFactorAuth->code_expires_at)) {
            throw new \Exception('Verification code has expired');
        }

        $twoFactorAuth->update([
            'verification_code' => null,
            'code_expires_at' => null,
        ]);

        return true;
    }

    public function disableTwoFactor(User $user): void
    {
        $twoFactorAuth = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactorAuth) {
            throw new \Exception('2FA is not enabled for this user');
        }

        $twoFactorAuth->delete();
    }

    private function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendVerificationCode(User $user, string $email, string $code): void
    {
        Mail::to($email)->send(new OtpEmail($user, $code));
    }
}
