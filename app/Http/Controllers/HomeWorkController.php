<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\HomeworkTemplate;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeworkController extends Controller
{


    private function createOrUpdateNotification(Homework $homework, Request $request)
    {
        $notification = Notification::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'type' => 'Homework',
                'entity_id' => $homework->id,
            ],
            [
                'show_at' => $homework->remind_at,
                'status' => 'Pending',
                'title' => 'Homework Reminder',
                'description' => $request->notification_message ?? $homework->title,
                'repeat' => $request->repeat,
            ]
        );

        $homework->notification_id = $notification->id;
    }

    private function removeNotification(Homework $homework)
    {
        Notification::where('entity_id', $homework->id)
            ->where('type', 'Homework')
            ->delete();

        $homework->notification_id = null;
    }


    public function index()
    {
        // if date is in params
        if (request()->has('date')) {
            $date = request('date');
            $dayStart = Carbon::parse($date)->startOfDay();
            $dayEnd = Carbon::parse($date)->endOfDay();
            $homeworks = Homework::where('user_id', auth()->id())
                ->whereBetween('deadline', [$dayStart, $dayEnd])
                ->with('notification')
                ->get();
            return response()->json($homeworks);
        }
        $homeworks = Homework::where('user_id', auth()->id())
            ->with('notification')
            ->get();
        return response()->json($homeworks);
    }

    public function activity()
    {
        $user_id = auth()->id();
        $today = now();
        $threeDaysFromNow = $today->copy()->addDays(3);

        // Count of incomplete homeworks
        $incompleteCount = Homework::where('user_id', $user_id)
            ->whereNull('completed_at')
            ->count();

        // Overdue or approaching deadline homeworks
        $urgentHomeworks = Homework::where('user_id', $user_id)
            ->where(function ($query) use ($today, $threeDaysFromNow) {
                $query->where('deadline', '<=', $today)
                    ->orWhereBetween('deadline', [$today, $threeDaysFromNow]);
            })
            ->whereNull('completed_at')
            ->orderBy('deadline')
            ->take(5)
            ->get();

        return response()->json([
            'incomplete_count' => $incompleteCount,
            'urgent_homeworks' => $urgentHomeworks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'deadline' => 'required|string',
            'completed_at' => 'nullable|string',
            'remind_at' => 'nullable|string',
            'notification_message' => 'nullable|string',
            'repeat' => 'nullable|string',
        ]);

        $request['user_id'] = auth()->id();
        $request['deadline'] = Carbon::parse($request['deadline']);
        if ($request['completed_at']) {
            $request['completed_at'] = Carbon::parse($request['completed_at']);
        }
        if ($request['remind_at']) {
            $request['remind_at'] = Carbon::parse($request['remind_at']);

            $notification = Notification::create([
                'user_id' => $request['user_id'],
                'show_at' => $request['remind_at'],
                'status' => 'Pending',
                'type' => 'Homework',
                'title' => 'Homework Reminder',
                'description' => $request['notification_message'] || $request['title'],
                'repeat' => $request['repeat'],
                'entity_id' => $request['id'],
            ]);

            $request['notification_id'] = $notification->id;
        }

        // $template = HomeworkTemplate::firstOrCreate(
        //     ['title' => $request->title, 'user_id' => $request->user_id],
        //     ['title' => $request->title, 'notification_message' => $request->notification_message]
        // );

        $homework = Homework::create($request->all());

        return response()->json($homework, 201);
    }

    public function show($id)
    {
        $homework = Homework::findOrFail($id);
        return response()->json($homework);
    }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }


    public function update(Request $request, Homework $homework)
    {
        if ($homework->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string',
            'deadline' => 'sometimes|required|date',
            'completed_at' => 'sometimes|nullable|date',
            'remind_at' => 'sometimes|nullable|date',
            'notification_message' => 'sometimes|nullable|string',
            'repeat' => 'sometimes|nullable|string',
        ]);

        $oldCompletedAt = $homework->completed_at;
        $oldRemindAt = $homework->remind_at;

        $homework->fill($request->all());

        // Parse dates
        $homework->deadline = $request->has('deadline') ? Carbon::parse($request->deadline) : $homework->deadline;
        $homework->completed_at = $request->has('completed_at') ? Carbon::parse($request->completed_at) : $homework->completed_at;
        $homework->remind_at = $request->has('remind_at') ? Carbon::parse($request->remind_at) : $homework->remind_at;

        // Handle notification based on completed_at and remind_at
        if ($homework->completed_at) {
            // Homework is completed, remove notification
            $this->removeNotification($homework);
        } elseif ($homework->remind_at) {
            // Homework is not completed and has a remind_at date
            if (!$oldRemindAt || $oldRemindAt != $homework->remind_at) {
                // Create or update notification
                $this->createOrUpdateNotification($homework, $request);
            }
        } elseif (!$homework->remind_at && $oldRemindAt) {
            // remind_at was removed, delete notification
            $this->removeNotification($homework);
        }

        // If homework was completed before and now it's not
        if ($oldCompletedAt && !$homework->completed_at && $homework->remind_at) {
            $this->createOrUpdateNotification($homework, $request);
        }

        $homework->save();

        return response()->json($homework);
    }

    public function destroy($id)
    {
        $homework = Homework::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($homework->notification_id) {
            // $notification = Notification::where('user_id', auth()->id())
            //     ->where('id', $homework->notification_id)
            //     ->firstOrFail();

            // $notification->delete();

            Notification::where('entity_id', $id)
                ->where('type', 'Homework')
                ->delete();
        }
        $homework->delete();

        return response()->json(null, 204);
    }
}
