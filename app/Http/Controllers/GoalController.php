<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalTemplate;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('date')) {
            $date = $request->input('date');
            $dayStart = Carbon::parse($date)->startOfDay();
            $dayEnd = Carbon::parse($date)->endOfDay();
            $goals = Goal::where('user_id', auth()->id())
                ->whereBetween('remind_at', [$dayStart, $dayEnd])
                ->with('notification')
                ->get();
        } else {
            $goals = Goal::where('user_id', auth()->id())
                ->with('notification')
                ->get();
        }
        return response()->json($goals);
    }

    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     'title' => 'required|string',
        //     'note' => 'nullable|string',
        //     'completed_at' => 'nullable|date',
        //     'notification_message' => 'nullable|string',
        //     'remind_at' => 'nullable|date',
        //     'repeat' => 'nullable|json',
        // ], [
        //     'title.required' => 'The title field is required.',
        // ]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'note' => 'nullable|string',
            'completed_at' => 'nullable|date',
            'notification_message' => 'nullable|string',
            'remind_at' => 'nullable|date',
            'repeat' => 'nullable|json',
        ], [
            'title.required' => 'The title field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validatedData = $validator->validated();

        $validatedData['user_id'] = auth()->id();
        if (isset($validatedData['remind_at'])) {
            $validatedData['remind_at'] = Carbon::parse($validatedData['remind_at']);
        }
        if (isset($validatedData['completed_at'])) {
            $validatedData['completed_at'] = Carbon::parse($validatedData['completed_at']);
        }


        if (isset($validatedData['remind_at'])) {
            $notification = Notification::create([
                'user_id' => $validatedData['user_id'],
                'show_at' => $validatedData['remind_at'],
                'status' => 'Pending',
                'type' => 'Goal',
                'title' => 'Goal Reminder',
                'description' => $validatedData['notification_message'] ?? $validatedData['title'],
                'repeat' => $validatedData['repeat'],
                'entity_id' => null, // We'll update this after creating the goal
            ]);

            $validatedData['notification_id'] = $notification->id;
        }


        $goal = Goal::create($validatedData);

        if (isset($notification)) {
            $notification->update(['entity_id' => $goal->id]);
        }


        GoalTemplate::firstOrCreate(
            ['title' => $validatedData['title'], 'user_id' => $validatedData['user_id']],
            $validatedData
        );

        return response()->json($goal, 201);
    }

    public function show(Request $request, $id)
    {
        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);
        return response()->json($goal);
    }

    public function update(Request $request, $id)
    {
        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);

        // $validatedData = $request->validate([
        //     'title' => 'sometimes|required|string',
        //     'note' => 'nullable|string',
        //     'completed_at' => 'nullable|date',
        //     'notification_message' => 'nullable|string',
        //     'remind_at' => 'nullable|date',
        //     'repeat' => 'nullable|json',
        // ]);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string',
            'note' => 'nullable|string',
            'completed_at' => 'nullable|date',
            'notification_message' => 'nullable|string',
            'remind_at' => 'nullable|date',
            'repeat' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validatedData = $validator->validated();

        if (isset($validatedData['remind_at'])) {
            $validatedData['remind_at'] = Carbon::parse($validatedData['remind_at']);
        }
        if (isset($validatedData['completed_at'])) {
            $validatedData['completed_at'] = Carbon::parse($validatedData['completed_at']);
        }

        if (!$goal->notification_id && isset($validatedData['remind_at'])) {
            // Create Notification
            $notification = Notification::create([
                'user_id' => auth()->id(),
                'show_at' => $validatedData['remind_at'],
                'status' => 'Pending',
                'type' => 'Goal',
                'title' => 'Goal Reminder',
                'description' => $validatedData['notification_message'] ?? $validatedData['title'] ?? $goal->title,
                'repeat' => $validatedData['repeat'] ?? null,
                'entity_id' => $goal->id,
            ]);

            $validatedData['notification_id'] = $notification->id;
        } elseif ($goal->notification_id && !isset($validatedData['remind_at'])) {
            // Delete Notification
            if ($goal->notification_id) {
                Notification::where('user_id', auth()->id())
                    ->where('id', $goal->notification_id)
                    ->delete();
            }
            $validatedData['notification_id'] = null;
        } elseif ($goal->notification_id && isset($validatedData['remind_at'])) {
            // Update Notification
            Notification::where('user_id', auth()->id())
                ->where('id', $goal->notification_id)
                ->update([
                    'show_at' => $validatedData['remind_at'],
                    'description' => $validatedData['notification_message'] ?? $validatedData['title'] ?? $goal->title,
                    'repeat' => $validatedData['repeat'] ?? null,
                ]);
        }

        $goal->update($validatedData);

        return response()->json($goal);
    }

    public function destroy(Request $request, $id)
    {
        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);

        if ($goal->notification_id) {
            Notification::where('user_id', auth()->id())
                ->where('id', $goal->notification_id)
                ->delete();
        }

        $goal->delete();

        return response()->json(null, 204);
    }
}
