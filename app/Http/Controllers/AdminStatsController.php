<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\YearStats;
use App\Models\MonthStats;

class AdminStatsController extends Controller
{
    public function yearly_stats(Request $request) {
        $year = $request->input('year', Carbon::now()->year);

        $stats = YearStats::where('year', $year)->first();

        if (!$stats) {
            return response()->json(['message' => 'No stats found for the given year'], 404);
        }

        return response()->json($stats);
    }

    public function monthly_stats(Request $request) {
        $date = $request->input('date', Carbon::now()->startOfMonth());

        $stats = MonthStats::whereDate('date', $date)->first();

        if (!$stats) {
            return response()->json(['message' => 'No stats found for the given month'], 404);
        }

        return response()->json($stats);
    }
}
