<?php

namespace App\Http\Controllers;

use App\Models\UserExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserExperienceController extends Controller
{
    public function index()
    {
        $userExperience = Auth::user()->userExperience;
        return response()->json($userExperience);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'has_add_goal' => 'boolean',
            'has_add_homework' => 'boolean',
            'has_add_mood' => 'boolean',
            'has_add_symptom' => 'boolean',
            'has_add_note' => 'boolean',
        ]);

        $userExperience = Auth::user()->userExperience()->create($validated);
        return response()->json($userExperience, 201);
    }

    public function show(UserExperience $userExperience)
    {
        if (Auth::id() !== $userExperience->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($userExperience);
    }

    public function update(Request $request, UserExperience $userExperience)
    {
        if (Auth::id() !== $userExperience->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'has_add_goal' => 'boolean',
            'has_add_homework' => 'boolean',
            'has_add_mood' => 'boolean',
            'has_add_symptom' => 'boolean',
            'has_add_note' => 'boolean',
        ]);

        $userExperience->update($validated);
        return response()->json($userExperience);
    }

    public function destroy(UserExperience $userExperience)
    {
        if (Auth::id() !== $userExperience->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $userExperience->delete();
        return response()->json(null, 204);
    }
}
