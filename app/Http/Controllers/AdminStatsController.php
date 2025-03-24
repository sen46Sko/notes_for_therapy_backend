<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\SystemAction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function stats(Request $request) {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'digits:4', 'min:0', 'max:' . date('Y')],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ]);

        $year = $validated['year'];
        $month = $validated['month'] ?? null;

        $interest = [
            SystemActionType::SUBSCRIPTION_MONTHLY,
            SystemActiontype::SUBSCRIPTION_YEARLY, 
            SystemActionType::SUBSCRIPTION_CANCELLED, 
            SystemActionType::TRIAL_STARTED, 
            SystemActionType::USER_REGISTERED,
            SystemActionType::USER_REGISTERED_VIA_APPLE,
            SystemActionType::USER_REGISTERED_VIA_GOOGLE,
            SystemActionType::USER_ACCOUNT_DELETED,
            SystemActionType::TICKET_RESOLVED,
            SystemActionType::TICKET_CREATED,
        ];

        $query = SystemAction::whereYear('created_at', $year)
            ->whereIn('action_type', $interest);

        if(!empty($month)) {
            $query->whereMonth('created_at', $month);
        }

        $logs = $query->get();
        $totalUsers = User::count();

        $stats = [
            'subscription_counter' => $logs->whereIn('action_type', [
                SystemActionType::SUBSCRIPTION_MONTHLY, SystemActionType::SUBSCRIPTION_YEARLY
            ])->count(),
            'trial_counter' => $logs->where('action_type', SystemActionType::TRIAL_STARTED)->count(),
            'cancel_counter' => $logs->where('action_type', SystemActionType::SUBSCRIPTION_CANCELLED)->count(),
            'signups' => $logs->whereIn('action_type', [
                SystemActionType::USER_REGISTERED, 
                SystemActionType::USER_REGISTERED_VIA_APPLE, 
                SystemActionType::USER_REGISTERED_VIA_GOOGLE
            ])->count(),
            'delete_account_counter' => $logs->where('action_type', SystemActionType::USER_ACCOUNT_DELETED)->count(),
            'resolved_tickets' => $logs->where('action_type', SystemActionType::TICKET_RESOLVED)->count(),
            'ticket_created' => $logs->where('action_type', SystemActionType::TICKET_CREATED)->count(),
            'total_users' => $totalUsers,
            'monthly_plan' => $logs->where('action_type', SystemActionType::SUBSCRIPTION_MONTHLY)->count(), 
            'yearly_plan' => $logs->where('action_type', SystemActionType::SUBSCRIPTION_YEARLY)->count(),
        ];

        return response()->json($stats);
    }
}
