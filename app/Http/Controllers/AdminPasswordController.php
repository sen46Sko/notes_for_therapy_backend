<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Services\PasswordPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminPasswordController extends Controller
{
    protected $passwordPolicyService;

    public function __construct(PasswordPolicyService $passwordPolicyService)
    {
        $this->passwordPolicyService = $passwordPolicyService;
    }

    public function changePassword(Request $request)
    {
        $admin = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|different:current_password',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $passwordErrors = $this->passwordPolicyService->validatePassword($request->new_password);
        if (!empty($passwordErrors)) {
            throw ValidationException::withMessages([
                'new_password' => $passwordErrors,
            ]);
        }

        $admin->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    public function checkPasswordExpiration()
    {
        $admin = auth()->user();
        $settings = \App\Models\SecuritySettings::first();

        if (!$settings || !$settings->periodic_password_changes_enabled) {
            return response()->json([
                'password_expired' => false
            ]);
        }

        if (!$admin->password_changed_at) {
            return response()->json([
                'password_expired' => true,
                'message' => 'You need to set a new password'
            ]);
        }

        $expirationDate = $this->calculateExpirationDate(
            $admin->password_changed_at,
            $settings->password_change_period
        );

        $isExpired = now()->greaterThan($expirationDate);

        return response()->json([
            'password_expired' => $isExpired,
            'expires_at' => $expirationDate->toIso8601String(),
            'message' => $isExpired ? 'Your password has expired' : 'Your password is valid'
        ]);
    }

    private function calculateExpirationDate($lastChangeDate, $period)
    {
        $date = clone $lastChangeDate;

        switch ($period) {
            case '1 month':
                return $date->addMonth();
            case '3 months':
                return $date->addMonths(3);
            case '6 months':
                return $date->addMonths(6);
            case '1 year':
                return $date->addYear();
            default:
                return $date->addMonths(3);
        }
    }
}