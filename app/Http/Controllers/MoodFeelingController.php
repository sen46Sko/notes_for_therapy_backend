<?php

// app/Http/Controllers/MoodFeelingController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoodFeeling;

class MoodFeelingController extends Controller
{
    public function index()
    {
        return response()->json(MoodFeeling::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:mood_feelings,name']);
        $moodFeeling = MoodFeeling::create($validated);
        return response()->json($moodFeeling, 201);
    }

    public function show($id)
    {
        return response()->json(MoodFeeling::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate(['name' => 'required|string|unique:mood_feelings,name']);
        $moodFeeling = MoodFeeling::findOrFail($id);
        $moodFeeling->update($validated);
        return response()->json($moodFeeling);
    }

    public function destroy($id)
    {
        $moodFeeling = MoodFeeling::findOrFail($id);
        $moodFeeling->delete();
        return response()->json(null, 204);
    }
}
