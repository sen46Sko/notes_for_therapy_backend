<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\ProblemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProblemController extends Controller
{
    public function index()
    {
        $problems = Problem::all();
        return response()->json($problems);
    }

public function store(Request $request)
{
    \Log::info('Incoming request data:', [
        'all_data' => $request->all(),
        'bearer_token' => $request->bearerToken()
    ]);

    $validatedData = $request->validate([
        'text' => 'required|string',
        'email' => 'required|email',
        'problem_id' => 'nullable|exists:problems,id',
        'problem_description' => 'sometimes|nullable|string',
    ]);

    $user = $request->user();

    $validatedData['user_id'] = $user->id;

    $problemRequest = ProblemRequest::create($validatedData);

    return response()->json($problemRequest, 201);
}
}
