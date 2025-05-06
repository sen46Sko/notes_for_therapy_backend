<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function updateActivity(Request $request)
    {
        return response()->json([
            'message' => 'Activity updated',
            'last_activity' => auth()->user()->last_activity_at
        ]);
    }
}