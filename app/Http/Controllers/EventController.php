<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Auth;
use App\Helper\NotificationRepeatHelper;
use App\Models\Notification;

class EventController extends Controller
{

    public function index()
    {
        // Extract date parameter from the request of format 'm-Y'
        $date = request()->date;
        // If date is not provided, then get the current month and year
        if (!$date) {
            $date = Carbon::now();
        }
        else {
            $date = Carbon::createFromFormat('m-Y', $date);
        }

        $startOfMonth = $date->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        // Extract month and year from the date

        $data = Event::where('user_id', Auth::user()->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'title' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'note' => 'nullable|string',
            'alert' => 'nullable|string',
            'repeat' => 'nullable|string',
        ]);

        $validatedData['date'] = Carbon::parse($validatedData['date']);
        $validatedData['start_at'] = Carbon::parse($validatedData['start_at']);
        $validatedData['end_at'] = Carbon::parse($validatedData['end_at']);

        $event = Event::create($validatedData + ['user_id' => Auth::user()->id]);

        if ($request['alert']) {
            $show_at = Carbon::parse($request['start_at'])->subMinutes($request['alert']);

            $alertNotification = Notification::create([
                'user_id' => $request['user_id'],
                'show_at' => $show_at,
                'status' => 'Pending',
                'type' => 'Event_Alert',
                'title' => 'Event Reminder',
                'description' => $request['notification_message'] || $request['title'],
                'repeat' => null,
                'entity_id' => $event->id,
            ]);
        }



        $notificationHelper = new NotificationRepeatHelper($request->repeat);
        $nextShowAt = $notificationHelper->getNextNotificationDate(
            Carbon::now()
        );

        $notification = Notification::create([
            'user_id' => $request['user_id'],
            'show_at' => $nextShowAt,
            'status' => 'Hidden',
            'type' => 'Event',
            'title' => $request['title'],
            'description' => $request['note'],
            'repeat' => $request['repeat'],
            'entity_id' => $event->id,
        ]);
        $event->notification_id = $notification->id;
        $event->save();


        return response()->json([
            'success' => true,
            'data' => $event,
            'message' => 'Created Successfully'
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $validatedData = $request->validate([
            'date' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'title' => 'required|string|max:255',
            'note' => 'nullable|string',
            'alert' => 'nullable|string',
            'repeat' => 'nullable|string',
        ]);

        $validatedData['date'] = Carbon::parse($validatedData['date']);
        $validatedData['start_at'] = Carbon::parse($validatedData['start_at']);
        $validatedData['end_at'] = Carbon::parse($validatedData['end_at']);

        if ($request['alert'] != null && $request['alert'] != $event->alert) {
            $show_at = Carbon::parse($request['start_at'])->subMinutes($request['alert']);

            Notification::where(
                'entity_id',
                $event->id
            )->where('type', 'Event_Alert')->delete();

            $alertNotification = Notification::create([
                'user_id' => $request['user_id'],
                'show_at' => $show_at,
                'status' => 'Pending',
                'type' => 'Event_Alert',
                'title' => 'Event Reminder',
                'description' => $request['notification_message'] || $request['title'],
                'repeat' => null,
                'entity_id' => $event->id,
            ]);
        }

        if ($request['repeat'] != null && $request['repeat'] != $event->repeat) {
            Notification::where(
                'entity_id',
                $event->id
            )->where('type', 'Event')->delete();

            $notificationHelper = new NotificationRepeatHelper($request->repeat);
            $nextShowAt = $notificationHelper->getNextNotificationDate(
                Carbon::now()
            );

            $notification = Notification::create([
                'user_id' => $request['user_id'],
                'show_at' => $nextShowAt,
                'status' => 'Hidden',
                'type' => 'Event',
                'title' => $request['title'],
                'description' => $request['note'],
                'repeat' => $request['repeat'],
                'entity_id' => $event->id,
            ]);
            $event->notification_id = $notification->id;
        }

        $event->fill($validatedData)->save();

        return response()->json([
            'success' => true,
            'data' => $event,
            'message' => "Event Updated Successfully"
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        Event::find($id)->delete();
        Notification::where('entity_id', $id)->where('type', 'Event')->delete();
        Notification::where('entity_id', $id)->where('type', 'Event_Alert')->delete();
        return response()->json([
            'success' => true,
            'message' => "Event Deleted Successfully"
        ], Response::HTTP_OK);
    }
}
