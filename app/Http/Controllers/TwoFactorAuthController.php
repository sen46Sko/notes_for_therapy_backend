<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuth;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class TwoFactorAuthController extends Controller
{
    protected $twoFactorAuthService;

    public function __construct(TwoFactorAuthService $twoFactorAuthService)
    {
        $this->twoFactorAuthService = $twoFactorAuthService;
    }

    public function initTwoFactor(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'email' => 'required|email',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

        try {
            $user = Auth::user();
            $this->twoFactorAuthService->initializeTwoFactor($user, 'email', $user->email);
            return response()->json([
                'message' => '2FA initialization successful. Please check your email for the verification code.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function verifyTwoFactor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = Auth::user();
            $this->twoFactorAuthService->verifyTwoFactor($user, $request->code);
            $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();
            return response()->json([
                'message' => '2FA has been successfully enabled.',
                'two_factor' => $twoFactor,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function disableTwoFactor()
    {
        try {
            $user = Auth::user();
            $this->twoFactorAuthService->disableTwoFactor($user);
            return response()->json([
                'message' => '2FA has been successfully disabled.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::findOrFail($request->user_id);
            if ($this->twoFactorAuthService->verifyCode($user, $request->code)) {
                // Generate JWT token here if using JWT
                $token = JWTAuth::fromUser($user);
                $profile = UserService::getUserProfile($user);
                return response()->json([
                    'message' => '2FA verification successful.',
                    'token' => $token,
                ] + $profile);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
