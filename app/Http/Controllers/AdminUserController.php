<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\SystemAction;
use App\Models\User;
use App\Services\SystemActionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    protected SystemActionService $systemActionService;

    public function __construct(SystemActionService $systemActionService)
    {
        $this->systemActionService = $systemActionService;
    }

    const WEEKS_IN_MONTH = 5;
    const MONTHS_IN_YEAR = 12;

    public function engagement(Request $request) {
        $interest = [
            SystemActionType::GOALS_INTERACTION,
            SystemActionType::HOMEWORKS_INTERACTION,
            SystemActionType::MOODS_INTERACTION,
            SystemActionType::NOTES_INTERACTION,
            SystemActionType::SYMPTOMPS_INTERACTION
        ];

        $today = Carbon::now();

        $currMonthStart = $today->copy()->startOfMonth();
        $currMonthEnd = $today->copy()->endOfMonth();

        $currYearStart = $today->copy()->startOfYear();
        $currYearEnd = $today->copy()->endOfYear();

        $monthlyStats = SystemAction::selectRaw('
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS goals,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS homeworks,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS moods,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS notes,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS symptoms
        ', $interest)
            ->whereBetween('created_at', [$currMonthStart, $currMonthEnd])
            ->first();

        $yearlyStats = SystemAction::selectRaw('
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS goals,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS homeworks,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS moods,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS notes,
            COUNT(CASE WHEN action_type = ? THEN 1 END) AS symptoms
        ', $interest)
        ->whereBetween('created_at', [$currYearStart, $currYearEnd])
        ->first();

        $weeklyGoals = [];
        $weeklyHomeworks = [];
        $weeklyMoods = [];
        $weeklyNotes = [];
        $weeklySymptomps = [];

        // Gather weekly stats for charts
        for ($i = 0; $i < self::WEEKS_IN_MONTH || $currMonthStart <= $currMonthEnd; $i++, $currMonthStart->addWeek()) { 
            $weekStart = $currMonthStart->copy()->startOfWeek();
            $weekEnd = $currMonthStart->copy()->endOfWeek();

            $weekEnd = min($weekEnd, $currMonthEnd);

            $weeklyStats = SystemAction::selectRaw('
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS goals,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS homeworks,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS moods,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS notes,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS symptoms
            ', $interest)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->first();

            $weeklyGoals[] = $weeklyStats->goals ?? 0;
            $weeklyHomeworks[] = $weeklyStats->homeworks ?? 0;
            $weeklyMoods[] = $weeklyStats->moods ?? 0;
            $weeklyNotes[] = $weeklyStats->notes ?? 0;
            $weeklySymptomps[] = $weeklyStats->symptoms ?? 0;
        }

        $monthlyGoals = [];
        $monthlyHomeworks = [];
        $monthlyMoods = [];
        $monthlyNotes = [];
        $monthlySymptomps = [];
        // Gather monthly stats for charts
        for($i = 0; $i < self::MONTHS_IN_YEAR || $currYearStart <= $currYearEnd; $i++, $currYearStart->addMonth()) {
            $monthStart = $currYearStart->copy()->startOfMonth();
            $monthEnd = $currYearStart->copy()->endOfMonth();

            $monthEnd = min($monthEnd, $currYearEnd);

            $monthlyStats = SystemAction::selectRaw('
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS goals,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS homeworks,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS moods,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS notes,
                COUNT(CASE WHEN action_type = ? THEN 1 END) AS symptoms
            ', $interest)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->first();

            $monthlyGoals[]     = $monthlyStats->goals ?? 0;
            $monthlyHomeworks[] = $monthlyStats->homeworks ?? 0;
            $monthlyMoods[]     = $monthlyStats->moods ?? 0;
            $monthlyNotes[]     = $monthlyStats->notes ?? 0;
            $monthlySymptomps[] = $monthlyStats->symptoms ?? 0;
        }

        $result = [
            'stats' => [
                'monthly' => $monthlyStatsQuery, 
                'yearly' => $yearlyStatsQuery
            ],
            'chartData' => [
                'monthly' => [
                    'goals' => $weeklyGoals,
                    'homeworks' => $weeklyHomeworks,
                    'moods' => $weeklyMoods,
                    'notes' => $weeklyNotes,
                    'symptomps' => $weeklySymptomps
                ],
                'yearly' => [
                    'goals' => $monthlyGoals,
                    'homeworks' => $monthlyHomeworks,
                    'moods' => $monthlyMoods,
                    'notes' => $monthlyNotes,
                    'symptomps' => $monthlySymptomps
                ]
            ]
        ];

        return response()->json($result);
    }

    public function users(Request $request) {

        $validated = $request->validate([
            "search" => ["nullable", "string"],
            "sub_plan" => ["nullable", "string"],
            "sub_status" => ["nullable", "string"],
            "gender" => ["nullable", "string"],
            "rows" => ["nullable", "integer", "min:1", "max:50"],
            "page" => ["nullable", "integer", "min:1"]
        ]);


        $search = $validated["search"] ?? null;
        $sub_plan = $validated['sub_plan'] ?? null;
        $sub_status = $validated['sub_status'] ?? null;
        $gender = $validated['gender'] ?? null;
        $rows = $validated['rows'] ?? 50;
        $page = $validated['page'] ?? 1;

        $users = User::join('subscriptions', 'subscriptions.user_id', '=', 'users.id')
        ->select(
            'users.id', 
            'users.name', 
            'users.email', 
            'users.gender', 
            'subscriptions.status as sub_status',
            'subscriptions.provider_subscription_id as sub_plan'
        )
            ->when($gender, function ($q, $gender) {
                return $q->where('gender', $gender);
            })
            ->when($search, function ($q, $search) {
                return $q->whereRaw('MATCH (users.name, users.email) AGAINST (? IN BOOLEAN MODE)', ['+' . $search . '*']);
            })
            ->when($sub_status, function ($q, $sub_status) {
                return $q->where('subscriptions.status', $sub_status);
            })
            ->when($sub_plan, function ($q, $sub_plan) {
                return $q->where('subscriptions.provider_subscription_id', $sub_plan);
            })
            ->distinct('users.id')  
            ->paginate($rows);

       return response()->json($users);
    }
}
