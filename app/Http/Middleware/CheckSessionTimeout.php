<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SecuritySettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $admin = auth()->user();

        if (!$admin) {
            return $next($request);
        }

        $settings = SecuritySettings::first();

        if (!$settings || !$settings->session_timeout_enabled) {
            return $next($request);
        }

        $lastActivity = $admin->last_activity_at;

        if ($lastActivity) {
            $timeout = $this->parseTimeout($settings->session_timeout_duration);
            $inactiveTime = Carbon::now()->diffInMinutes($lastActivity);

            if ($inactiveTime >= $timeout) {

                Auth::guard('web')->logout();

                if ($request->user()) {
                    $request->user()->currentAccessToken()->delete();
                }

                return response()->json([
                    'message' => 'Session expired due to inactivity',
                    'session_expired' => true
                ], 401);
            }
        }

        return $next($request);
    }

    private function parseTimeout($duration)
    {
        switch ($duration) {
            case '1 minute':
                return 1;
            case '1 hour':
                return 60;
            case '3 hours':
                return 180;
            case '6 hours':
                return 360;
            default:
                return 60;
        }
    }
}