<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\Subscription;
use App\Models\SystemAction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Str;

class AdminStatsController extends Controller
{
    public function userActivity(Request $request) {

        $loginMethods = [
            SystemActionType::USER_LOGGED_IN,
            SystemActionType::USER_LOGGED_IN_VIA_APPLE,
            SystemActionType::USER_LOGGED_IN_VIA_GOOGLE,
        ];

        $today = Carbon::now();
        $prevMonth = Carbon::now()->subMonth();

        $activeUsersCurrentMonth = SystemAction::whereIn('action_type', $loginMethods)
            ->whereMonth('created_at',$today->month)
            ->count();
        
        $activeUsersPrevMonth = SystemAction::whereIn('action_type', $loginMethods)
            ->whereMonth('created_at', $prevMonth->month)
            ->count();

        $activeSubscriptionsCurrent = Subscription::where('status', 'active')->count();

        $activeSubscriptionsPrevMonth = Subscription::where('created_at', '<=', $prevMonth)
            ->where('expiration_date', '>', $prevMonth)
            ->count();

        
        $feedbackCurrentMonth = 126;
        $feedbackPrevMonth = 123;

        $totalUsersCurrently = User::count();
        $totalUsersPrevMonth = User::where('created_at', '<=', $prevMonth)->count();

        $response = [
            'activeUsers' => [ 'current' => $activeUsersCurrentMonth, 'prev' => $activeUsersPrevMonth],
            'subscriptions' => ['current' => $activeSubscriptionsCurrent, 'prev' => $activeSubscriptionsPrevMonth],
            'feedback' => ['current' => $feedbackCurrentMonth, 'prev' => $feedbackPrevMonth],
            'totalUsers' => ['current' => $totalUsersCurrently, 'prev' => $totalUsersPrevMonth]
        ];

        return response()->json($response);
    }

    public function stats(Request $request) {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'digits:4', 'min:0', 'max:' . date('Y')],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ]);

        $year = $validated['year'];
        $month = $validated['month'] ?? null;

        $interest = [
            SystemActionType::SUBSCRIPTION_MONTHLY,
            SystemActionType::SUBSCRIPTION_YEARLY,
            SystemActionType::SUBSCRIPTION_CANCELLED,
            SystemActionType::TRIAL_STARTED,
            SystemActionType::USER_REGISTERED,
            SystemActionType::USER_REGISTERED_VIA_APPLE,
            SystemActionType::USER_REGISTERED_VIA_GOOGLE,
            SystemActionType::USER_ACCOUNT_DELETED,
            SystemActionType::TICKET_RESOLVED,
            SystemActionType::TICKET_CREATED,
        ];

        $query = SystemAction::selectRaw("
            COUNT(CASE WHEN action_type IN (?, ?) THEN 1 END) AS subscription_counter,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS trial_counter,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS cancel_counter,
            COUNT(CASE WHEN action_type IN (?, ?, ?) THEN 1 END) AS signups,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS delete_account_counter,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS resolved_tickets,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS ticket_created,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS monthly_plan,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS yearly_plan
        ", [
            SystemActionType::SUBSCRIPTION_MONTHLY, SystemActionType::SUBSCRIPTION_YEARLY,
            SystemActionType::TRIAL_STARTED,
            SystemActionType::SUBSCRIPTION_CANCELLED,
            SystemActionType::USER_REGISTERED, SystemActionType::USER_REGISTERED_VIA_APPLE, SystemActionType::USER_REGISTERED_VIA_GOOGLE,
            SystemActionType::USER_ACCOUNT_DELETED,
            SystemActionType::TICKET_RESOLVED,
            SystemActionType::TICKET_CREATED,
            SystemActionType::SUBSCRIPTION_MONTHLY,
            SystemActionType::SUBSCRIPTION_YEARLY
        ])
        ->whereYear('created_at', $year)
        ->whereIn('action_type', $interest);

        if (!empty($month)) {
            $query->whereMonth('created_at', $month);
        }

        $stats = $query->first()->toArray();
        $stats['total_users'] = User::count();

        return response()->json($stats);
    }
}
