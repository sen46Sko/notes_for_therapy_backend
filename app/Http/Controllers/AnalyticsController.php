<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Homework;
use App\Models\Note;
use App\Models\UserSymptom;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function homeChart()
    {
        $now = Carbon::now();
        $weekStart = $now->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $yearStart = $now->copy()->startOfYear();

        $user = auth()->user();
        $userId = auth()->id();

        $weekGoals = Goal::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $weekStart)
            ->get();
        $weekHomeworks = Homework::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $weekStart)
            ->get();
        $weekSymptoms = UserSymptom::where('user_id', $userId)
            ->with('symptom')
            ->where('created_at', '>=', $weekStart)
            ->get();

        $monthGoals = Goal::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $monthStart)
            ->get();
        $monthHomeworks = Homework::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $monthStart)
            ->get();
        $monthSymptoms = UserSymptom::where('user_id', $userId)
            ->with('symptom')
            ->where('created_at', '>=', $monthStart)
            ->get();

        $yearGoals = Goal::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $yearStart)
            ->get();
        $yearHomeworks = Homework::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $yearStart)
            ->get();
        $yearSymptoms = UserSymptom::where('user_id', $userId)
            ->with('symptom')
            ->where('created_at', '>=', $yearStart)
            ->get();

        return response()->json([
            'week' => [
                'goals' => $weekGoals,
                'homeworks' => $weekHomeworks,
                'symptoms' => $weekSymptoms,
            ],
            'month' => [
                'goals' => $monthGoals,
                'homeworks' => $monthHomeworks,
                'symptoms' => $monthSymptoms,
            ],
            'year' => [
                'goals' => $yearGoals,
                'homeworks' => $yearHomeworks,
                'symptoms' => $yearSymptoms,
            ],
            'user_id' => $userId,
        ]);
    }

    public function homeGoals()
    {
        $goals = Goal::where('user_id', auth()->id())
            ->whereNull('completed_at')
            ->latest('created_at')
            ->take(5)
            ->get();

        return response()->json($goals);
    }

    public function homeHomeworks()
    {
        $now = Carbon::now();

        $homeworks = Homework::where('user_id', auth()->id())
            ->whereNull('completed_at')
            ->orderByRaw('CASE WHEN deadline < ? THEN 0 ELSE 1 END', [$now])
            ->orderBy('deadline')
            ->take(5)
            ->get();

        return response()->json($homeworks);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $userId = auth()->id();

        $goals = Goal::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('note', 'like', "%{$query}%");
            })
            ->get();

        $homeworks = Homework::where('user_id', $userId)
            ->where('title', 'like', "%{$query}%")
            ->get();

        $notes = Note::where('user_id', $userId)
            ->where('title', 'like', "%{$query}%")
            ->get();

        return response()->json([
            'goals' => $goals,
            'homeworks' => $homeworks,
            'notes' => $notes,
        ]);
    }
}
