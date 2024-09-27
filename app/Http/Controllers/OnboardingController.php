<?php

// app/Http/Controllers/OnboardingController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Onboarding;

class OnboardingController extends Controller
{
    public function index()
    {
        return response()->json(Onboarding::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // For each user, key should be unique
            'key' => 'required|string',
            'value' => 'required|boolean',
        ]);
        // $onboarding = Onboarding::create([
        //     'user_id' => auth()->id(),
        // ] + $validated);

        // Create or update based on user_id and key
        $onboarding = Onboarding::updateOrCreate(
            ['user_id' => auth()->id(), 'key' => $validated['key']],
            [
                'user_id' => auth()->id(),
            ] + $validated
        );
        return response()->json($onboarding, 201);
    }

    public function show($id)
    {
        return response()->json(Onboarding::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            // For each user, key should be unique
            'key' => 'required|string|exists:onboardings,key,NULL,id,user_id,' . auth()->id(),
            'value' => 'required|boolean',
            'user_id' => 'required|exists:users,id|in:' . auth()->id(),
        ]);
        $onboarding = Onboarding::findOrFail($id);
        $onboarding->update($validated);
        return response()->json($onboarding);
    }

    public function destroy($id)
    {
        $onboarding = Onboarding::findOrFail($id);
        $onboarding->delete();
        return response()->json(null, 204);
    }
}
