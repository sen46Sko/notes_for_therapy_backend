<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AdminRegisterLink;
use Illuminate\Http\Request;

class ValidateAdminRegistrationLink
{
    public function handle(Request $request, Closure $next)
    {
        $uuid = $request->route('uuid');

        $registrationLink = AdminRegisterLink::where('uuid', $uuid)->first();

        if (!$registrationLink) {
            return response()->json([
                'message' => 'Invalid or expired registration link'
            ], 404);
        }

        return $next($request);
    }
}