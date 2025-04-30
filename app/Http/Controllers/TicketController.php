<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProblemRequest;
use App\Models\ProblemLog;
use App\Models\ProblemMessage;
use App\Models\Subscription;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:in_progress,waiting,resolved,closed',
        ]);

        $ticket = ProblemRequest::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        if (in_array($request->status, ['resolved', 'closed'])) {
            ProblemLog::create([
                'ticket_id'   => $ticket->id,
                'action_type' => 'closed',
                'description' => 'Ticket closed by admin',
                'value'       => null,
            ]);
        } else {
            ProblemLog::create([
                'ticket_id'   => $ticket->id,
                'action_type' => 'status_updated',
                'description' => 'Status changed to ' . $request->status,
                'value'       => $request->status,
            ]);
        }

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function adminSendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $admin = auth()->user();

        $ticket = ProblemRequest::findOrFail($id);

        ProblemMessage::create([
            'ticket_id' => $ticket->id,
            'admin_id' => $admin->id,
            'text' => $request->message,
            'author' => 'admin',
        ]);

        ProblemLog::create([
            'ticket_id' => $ticket->id,
            'action_type' => 'admin_responded',
            'description' => 'Admin sent a message',
            'value' => null,
        ]);

        return response()->json(['message' => 'Message sent successfully']);
    }

    public function changeNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $ticket = ProblemRequest::findOrFail($id);
        $ticket->note = $request->note;
        $ticket->save();

        return response()->json(['message' => 'Note updated successfully']);
    }

    public function userSendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        $ticket = ProblemRequest::findOrFail($id);

        if ($ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!in_array($ticket->status, ['in_progress', 'waiting'])) {
            return response()->json(['error' => 'Ticket is not active'], 400);
        }

        if ($ticket->status === 'waiting') {
            $ticket->status = 'in_progress';
            $ticket->save();
        }

        ProblemMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'text' => $request->message,
            'author' => 'user',
        ]);

        ProblemLog::create([
            'ticket_id' => $ticket->id,
            'action_type' => 'user_responded',
            'description' => 'User sent a message',
            'value' => null,
        ]);

        return response()->json(['message' => 'Message sent']);
    }

public function getStats()
{
    $now = Carbon::now();
    $startOfYear = $now->copy()->startOfYear();
    $startOfLastYear = $now->copy()->subYear()->startOfYear();
    $endOfLastYear = $now->copy()->subYear()->endOfYear();

    return response()->json([
        'resolved' => [
            'prev' => ProblemLog::where('action_type', 'closed')
                        ->whereBetween('created_at', [$startOfLastYear, $endOfLastYear])
                        ->distinct('ticket_id')
                        ->count('ticket_id'),
            'current' => ProblemLog::where('action_type', 'closed')
                        ->where('created_at', '>=', $startOfYear)
                        ->distinct('ticket_id')
                        ->count('ticket_id')
        ],
        'pending' => [
            'prev' => 0,
            'current' => ProblemRequest::where('status', 'waiting')->count(),
        ],
        'total' => [
            'prev' => ProblemRequest::whereBetween('created_at', [$startOfLastYear, $endOfLastYear])->count(),
            'current' => ProblemRequest::where('created_at', '>=', $startOfYear)->count(),
        ],
        'feedback' => [
            'prev' => 0,
            'current' => 0,
        ],
    ]);
}

public function listTickets(Request $request)
{
    $query = ProblemRequest::with(['user', 'problem:id,title']);

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('from')) {
        $query->where('created_at', '>=', Carbon::parse($request->from));
    }

    if ($request->filled('to')) {
        $query->where('created_at', '<=', Carbon::parse($request->to));
    }

    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);

    $tickets = $query
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json($tickets);
}

public function getTicketDetails($id)
{
    $ticket = ProblemRequest::with([
        'user',
        'problem',
        'logs',
        'messages' => fn($q) => $q->orderBy('created_at')
    ])->findOrFail($id);

    $user = $ticket->user;

    if (!$user) {
        return response()->json([
            'id' => $ticket->id,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at,
            'status' => $ticket->status,
            'problem_description' => $ticket->problem_description,
            'note' => $ticket->note,
            'problem' => [
                'name' => optional($ticket->problem)->title,
            ],
            'logs' => $ticket->logs,
            'messages' => $ticket->messages,
            'user' => null,
        ]);
    }

    $subscription = Subscription::where('user_id', $user->id)->latest()->first();
    $plan = null;


    if ($subscription) {
        if (str_starts_with($subscription->name, 'notes_monthly_')) {
            $plan = 'monthly';
        } elseif (str_starts_with($subscription->name, 'notes_yearly_')) {
            $plan = 'yearly';
        } elseif (str_starts_with($subscription->name, 'notes_quarterly_')) {
            $plan = 'quarterly';
        }
    }

    return response()->json([
        'id' => $ticket->id,
        'created_at' => $ticket->created_at,
        'updated_at' => $ticket->updated_at,
        'status' => $ticket->status,
        'problem_description' => $ticket->problem_description,
        'note' => $ticket->note,
        'problem' => [
            'name' => optional($ticket->problem)->name,
        ],
        'logs' => $ticket->logs,
        'messages' => $ticket->messages,
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image,
            'created_at' => $user->created_at,
            'last_login' => $user->last_login ?? null,
            'gender' => $user->gender,
            'birthdate' => $user->birthdate,
            'plan' => $plan,
        ],
    ]);
}
public function getUserTickets(Request $request)
{
    $user = auth()->user();

    $tickets = ProblemRequest::where('user_id', $user->id)
                              ->with(['problem', 'logs', 'messages'])
                              ->get();

    return response()->json($tickets);
}


}
