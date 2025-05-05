<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Services\AdminTwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminTwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(AdminTwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function enable(Request $request)
    {
        $admin = auth()->user();

        $this->twoFactorService->enableTwoFactor($admin);
        $this->twoFactorService->generateAndSendCode($admin);

        return response()->json([
            'message' => 'Two-factor authentication enabled. Please check your email for the verification code.',
            'requires_code' => true
        ]);
    }

    public function disable(Request $request)
    {
        $admin = auth()->user();

        $this->twoFactorService->disableTwoFactor($admin);

        return response()->json([
            'message' => 'Two-factor authentication disabled successfully.'
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $admin = auth()->user();

        if ($this->twoFactorService->verifyCode($admin, $request->code)) {
            return response()->json([
                'message' => 'Two-factor authentication verified successfully.'
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired verification code.'
        ], 422);
    }

    public function verifyDuringLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !$this->twoFactorService->verifyCode($admin, $request->code)) {
            return response()->json([
                'message' => 'Invalid or expired verification code.'
            ], 422);
        }

        $admin->update(['last_activity_at' => now()]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => $admin,
            'role' => $admin->role,
            'rights' => $admin->rights
        ]);
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !$admin->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled for this account.'
            ], 422);
        }

        $this->twoFactorService->generateAndSendCode($admin);

        return response()->json([
            'message' => 'Verification code resent successfully.'
        ]);
    }
}