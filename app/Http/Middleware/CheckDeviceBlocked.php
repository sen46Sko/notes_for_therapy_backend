<?php

namespace App\Http\Middleware;

use App\Models\AdminDevice;
use Closure;
use Illuminate\Http\Request;

class CheckDeviceBlocked
{
    public function handle(Request $request, Closure $next)
    {
        $admin = auth()->user();

        if (!$admin) {
            return $next($request);
        }

        $deviceId = $request->header('X-Device-ID');

        if ($deviceId) {
            $device = AdminDevice::where('id', $deviceId)
                ->where('admin_id', $admin->id)
                ->first();

            if ($device) {
                if ($device->is_blocked) {
                    return response()->json([
                        'message' => 'Your device has been blocked. Please contact administrator.'
                    ], 403);
                }

                if (!$device->is_active) {
                    return response()->json([
                        'message' => 'Your session has been terminated.'
                    ], 403);
                }

                $device->update(['last_active_at' => now()]);
            }
        }

        return $next($request);
    }
}