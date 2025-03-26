<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\Goal;
use App\Models\Notification;
use App\Services\SystemActionService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    protected SystemActionService $systemActionService;

    public function __construct(SystemActionService $systemActionService)
    {
        $this->systemActionService = $systemActionService;
    }
    private function createOrUpdateNotification(Goal $goal, Request $request)
    {
        $notification = Notification::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'type' => 'Goal',
                'status' => 'Pending',
                'entity_id' => $goal->id,
            ],
            [
                'show_at' => $goal->remind_at,
                'status' => 'Pending',
                'title' => 'Goal Reminder',
                'description' => $request->notification_message ?? $goal->title,
                'repeat' => $request->repeat,
            ]
        );

        $goal->notification_id = $notification->id;
    }

    private function removeNotification(Goal $goal)
    {
        Notification::where('entity_id', $goal->id)
            ->where('type', 'Goal')
            ->where('status', 'Pending')
            ->delete();

        $goal->notification_id = null;
    }

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

        $this->systemActionService->logAction(SystemActionType::GOALS, [
            'user_id' => auth()->id()
        ]);

        return response()->json($goals);
    }

    public function store(Request $request)
    {
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

        $goal = Goal::create($validatedData);

        if (isset($validatedData['remind_at'])) {
            $this->createOrUpdateNotification($goal, $request);
        }

        $goal->save();

        $this->systemActionService->logAction(SystemActionType::GOALS, [
            'user_id' => auth()->id()
        ]);

        return response()->json($goal, 201);
    }

    public function show(Request $request, $id)
    {
        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);
        
        $this->systemActionService->logAction(SystemActionType::GOALS, [
            'user_id' => auth()->id()
        ]);

        return response()->json($goal);
    }

    public function update(Request $request, $id)
    {
        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);

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

        $oldCompletedAt = $goal->completed_at;
        $oldRemindAt = $goal->remind_at;

        $goal->fill($validatedData);

        if (isset($validatedData['remind_at'])) {
            $goal->remind_at = Carbon::parse($validatedData['remind_at']);
        }
        if (isset($validatedData['completed_at'])) {
            $goal->completed_at = Carbon::parse($validatedData['completed_at']);
        }

        if ($goal->completed_at) {
            $this->removeNotification($goal);
        } elseif ($goal->remind_at) {
            if (!$oldRemindAt || $oldRemindAt != $goal->remind_at) {
                $this->createOrUpdateNotification($goal, $request);
            }
        } elseif (!$goal->remind_at && $oldRemindAt) {
            $this->removeNotification($goal);
        }

        if ($oldCompletedAt && !$goal->completed_at && $goal->remind_at) {
            $this->createOrUpdateNotification($goal, $request);
        }

        $goal->save();

        $this->systemActionService->logAction(SystemActionType::GOALS, [
            'user_id' => auth()->id()
        ]);

        return response()->json($goal);
    }

    public function destroy(Request $request, $id)
    {
        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);

        $this->removeNotification($goal);
        $goal->delete();

        $this->systemActionService->logAction(SystemActionType::GOALS, [
            'user_id' => auth()->id()
        ]);

        return response()->json(null, 204);
    }
}
