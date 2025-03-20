<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\SystemAction;
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
            SystemActionType::SUBSCRIPTION, 
            SystemActionType::SUBSCRIPTION_CANCELLED, 
            SystemActionType::TRIAL_STARTED, 
            SystemActionType::USER_REGISTERED,
            SystemActionType::USER_REGISTERED_VIA_APPLE,
            SystemActionType::USER_REGISTERED_VIA_GOOGLE,
            SystemActionType::USER_ACCOUNT_DELETED,
        ];

        $query = SystemAction::whereYear('created_at', $year)
            ->whereIn('action_type', $interest);

        if(!empty($month)) {
            $query->whereMonth('created_at', $month);
        }

        $logs = $query->get();

        return response()->json($logs);
    }
}
