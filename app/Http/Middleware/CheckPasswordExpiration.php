<?php

namespace App\Http\Middleware;

use App\Models\SecuritySettings;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CheckPasswordExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $admin = auth()->user();

        if (!$admin) {
            return $next($request);
        }

        $settings = SecuritySettings::first();

        if (!$settings || !$settings->periodic_password_changes_enabled) {
            return $next($request);
        }

        if ($admin->password_changed_at) {
            $expirationDate = $this->calculateExpirationDate($admin->password_changed_at, $settings->password_change_period);

            if (Carbon::now()->greaterThan($expirationDate)) {
                if (!$request->is('api/admin/change-password') && !$request->is('api/admin/logout')) {
                    return response()->json([
                        'message' => 'Your password has expired. Please change it.',
                        'password_expired' => true
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    private function calculateExpirationDate($lastChangeDate, $period)
    {
        $date = Carbon::parse($lastChangeDate);

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