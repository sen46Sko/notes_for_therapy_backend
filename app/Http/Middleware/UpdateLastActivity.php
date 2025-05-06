<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UpdateLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = auth()->user()) {
            $user->update(['last_activity_at' => Carbon::now()]);
        }

        return $next($request);
    }
}