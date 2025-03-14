<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $admin = auth()->user();

        if (!$admin || !$admin->rights || !$admin->rights->$permission) {
            return response()->json([
                'message' => 'You do not have the required permission: ' . $permission
            ], 403);
        }

        return $next($request);
    }
}
