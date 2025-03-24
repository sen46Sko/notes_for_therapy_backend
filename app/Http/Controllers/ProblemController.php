<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\ProblemRequest;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function index()
    {
        $problems = Problem::all();
        return response()->json($problems);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'text' => 'required|string',
            'email' => 'required|email',
            'problem_id' => 'nullable|exists:problems,id',
            'problem_description' => 'sometimes|nullable|string',
        ]);

        $problemRequest = ProblemRequest::create($validatedData);

        return response()->json($problemRequest, 201);
    }
}
