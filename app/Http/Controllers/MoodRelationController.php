<?php

// app/Http/Controllers/MoodRelationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoodRelation;

class MoodRelationController extends Controller
{
    public function index()
    {
        return response()->json(MoodRelation::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:mood_relations,name']);
        $moodRelation = MoodRelation::create($validated);
        return response()->json($moodRelation, 201);
    }

    public function show($id)
    {
        return response()->json(MoodRelation::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate(['name' => 'required|string|unique:mood_relations,name']);
        $moodRelation = MoodRelation::findOrFail($id);
        $moodRelation->update($validated);
        return response()->json($moodRelation);
    }

    public function destroy($id)
    {
        $moodRelation = MoodRelation::findOrFail($id);
        $moodRelation->delete();
        return response()->json(null, 204);
    }
}
