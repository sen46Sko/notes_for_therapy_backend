<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('sanctum')->user() || !auth('sanctum')->user() instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $admin = auth('sanctum')->user();

        // Check if admin is deactivated
        if ($admin->deactivate_to && now()->lt($admin->deactivate_to)) {
            return response()->json([
                'message' => 'Your account is deactivated until ' . $admin->deactivate_to->format('Y-m-d H:i:s')
            ], 403);
        }

        // Check if admin status is pending
        if ($admin->status === 'pending') {
            return response()->json([
                'message' => 'Your account is pending activation'
            ], 403);
        }

        return $next($request);
    }
}
