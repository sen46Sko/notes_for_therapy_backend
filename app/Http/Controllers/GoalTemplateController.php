<?php

namespace App\Http\Controllers;

use App\Models\GoalTemplate;
use Illuminate\Http\Request;

class GoalTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = GoalTemplate::get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'notification_message' => 'nullable|string',
            'remind_at' => 'nullable|date',
            'repeat' => 'nullable|array',
            'repeat.type' => 'required_with:repeat|in:weekdays,weekends,daily,biweekly,weekly,monthly',
            'repeat.custom' => 'nullable|array',
        ]);

        // $validatedData['user_id'] = $request->user()->id;

        $template = GoalTemplate::create($validatedData);
        return response()->json($template, 201);
    }

    public function show(Request $request, $id)
    {
        $template = GoalTemplate::findOrFail($id);
        return response()->json($template);
    }

    public function update(Request $request, $id)
    {
        $template = GoalTemplate::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string',
            'notification_message' => 'nullable|string',
            'remind_at' => 'nullable|date',
            'repeat' => 'nullable|array',
            'repeat.type' => 'required_with:repeat|in:weekdays,weekends,daily,biweekly,weekly,monthly',
            'repeat.custom' => 'nullable|array',
        ]);

        $template->update($validatedData);
        return response()->json($template);
    }

    public function destroy(Request $request, $id)
    {
        $template = GoalTemplate::findOrFail($id);
        $template->delete();
        return response()->json(null, 204);
    }
}
