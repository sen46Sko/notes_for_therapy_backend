<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        // Extract page and limitPerPage from query params
        $page = request('page', 1);
        $limitPerPage = request('limitPerPage', 10);
        // $notifications = Notification::where('user_id', Auth::id())->get();
        $sentNotifications = Notification::where('user_id', Auth::id())
            ->where('status', '=', 'Sent')
            // It should be ordered by show_at, but be shown with status -> Sent first
            ->orderBy('show_at', 'desc')
            ->skip(($page - 1) * $limitPerPage)
            ->take($limitPerPage)
            ->get();
        $restOfNotifications = Notification::where('user_id', Auth::id())
            ->where('status', '!=', 'Hidden')
            ->where('status', '!=', 'Pending')
            ->where('status', '!=', 'Sent')
            ->orderBy('show_at', 'desc')
            ->skip(($page - 1) * $limitPerPage)
            ->take($limitPerPage - count($sentNotifications))
            ->get();

        $totalSent = Notification::where('user_id', Auth::id())
            ->where('status', '=', 'Sent')
            ->count();
        // Total count of not hidden notifications
        $total = Notification::where('user_id', Auth::id())
            ->where('status', '!=', 'Hidden')
            ->where('status', '!=', 'Pending')
            ->count();

        $notifications = $sentNotifications->merge($restOfNotifications);
        $hasNextPage = $total > $page * $limitPerPage;

        return response()->json([
            'data' => $notifications,
            'totalSent' => $totalSent,
            'total' => $total,
            'hasNextPage' => $hasNextPage,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'show_at' => 'required|date',
            'status' => 'required|in:Pending,Sent,Seen,Hidden',
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'repeat' => 'nullable|string',
            'entity_id' => 'nullable|integer',
        ]);

        $notification = Notification::create([
            'user_id' => Auth::id(),
            'show_at' => $request->show_at,
            'status' => $request->status,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'repeat' => $request->repeat,
            'entity_id' => $request->entity_id,
        ]);

        return response()->json($notification, 201);
    }

    public function show($id)
    {
        $notification = Notification::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return response()->json($notification);
    }

    public function seen(Request $request) {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('status', '=', 'Sent')
            ->get();
        foreach ($notifications as $notification) {
            $notification->update(['status' => 'Seen']);
        }
        return response()->json($notifications);
    }

    public function seenById($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $notification->update(['status' => 'Seen']);
        return response()->json($notification);
    }

    public function hide(Request $request) {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('status', '=', 'Seen')
            ->orWhere('status', '=', 'Sent')
            ->get();
        foreach ($notifications as $notification) {
            $notification->update(['status' => 'Hidden']);
        }
        return response()->json($notifications);
    }

    public function hideById($id)
    {
        $notification = Notification::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $notification->update(['status' => 'Hidden']);
        return response()->json($notification);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'show_at' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Pending,Sent,Seen,Hidden',
            'type' => 'sometimes|required|string',
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'repeat' => 'sometimes|nullable|string',
            'entity_id' => 'sometimes|nullable|integer',
        ]);

        $notification = Notification::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $notification->update($request->all());

        return response()->json($notification);
    }

    public function destroy($id)
    {
        $notification = Notification::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $notification->delete();

        return response()->json(null, 204);
    }
}
