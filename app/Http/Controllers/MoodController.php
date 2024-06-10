<?php
// app/Http/Controllers/MoodController.php
namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\MoodFeeling;
use App\Models\MoodRelation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MoodController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|integer|between:0,100',
            'type' => 'required|in:momentary,daily',
            'mood_relation_id' => 'required|exists:mood_relations,id',
            'mood_feelings' => 'array',
            'mood_feelings.*' => 'exists:mood_feelings,id',
            'note' => 'nullable|string',
        ]);

        $mood = Mood::create([
            'value' => $validated['value'],
            'type' => $validated['type'],
            'mood_relation_id' => $validated['mood_relation_id'],
            'note' => $validated['note'],
            'user_id' => auth()->id(),
        ]);

        if (!empty($validated['mood_feelings'])) {
            $mood->moodFeelings()->attach($validated['mood_feelings']);
        }

        return response()->json($mood->load('moodRelation', 'moodFeelings'), 201);
    }

    public function destroy($id)
    {
        try {
            $moodFeeling = Mood::findOrFail($id);
            if ($moodFeeling->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $moodFeeling->delete();
            return response()->json(null, 204);
        } catch(\Exception $e){
            return response()->json(['error' => 'Mood not found'], 404);
        }
    }

    public function getMoodByDate(Request $request)
    {
        $date = Carbon::createFromFormat('d-m-Y', $request->query('date'))->startOfDay();
        // Get all moods in the current day
        $moods = Mood::where('user_id', auth()->id())
            ->where('created_at', '>=', $date)
            ->where('created_at', '<', $date->copy()->addDay())
            ->with(['moodRelation', 'moodFeelings'])
            ->get();

        $momentaryMoods = $moods->where('type', 'momentary')->values();

        return response()->json([
            "daily" => $moods->where('type', 'daily')->first(),
            "momentary" => $momentaryMoods,
        ]);
    }

    public function getMoodInfo()
    {
        $relations = MoodRelation::all();
        $feelings = MoodFeeling::all();

        return response()->json([
            'relations' => $relations,
            'feelings' => $feelings,
        ]);
    }

    public function getWeeklyReport()
    {
        $userId = auth()->id();
        // Start from Monday
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $moods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $byDays = array_fill(0, 7, []);
        $currentWeekPositiveCount = 0;
        $currentWeekNegativeCount = 0;

        foreach ($moods as $mood) {
            $dayIndex = (Carbon::parse($mood->created_at)->dayOfWeek + 6) % 7;
            $byDays[$dayIndex][] = $mood->value;

            if ($mood->value > 55) {
                $currentWeekPositiveCount++;
            } elseif ($mood->value < 45) {
                $currentWeekNegativeCount++;
            }
        }

        $currentWeekTotalCount = $currentWeekPositiveCount + $currentWeekNegativeCount;

        $previousWeekMoods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek->copy()->subWeek(), $endOfWeek->copy()->subWeek()])
            ->get();

        $previousWeekPositiveCount = 0;
        $previousWeekNegativeCount = 0;

        foreach ($previousWeekMoods as $mood) {
            if ($mood->value > 55) {
                $previousWeekPositiveCount++;
            } elseif ($mood->value < 45) {
                $previousWeekNegativeCount++;
            }
        }
        $previousWeekTotalCount = $previousWeekPositiveCount + $previousWeekNegativeCount;

        $previousWeekPositivePercentage = $previousWeekTotalCount > 0 ? ($previousWeekPositiveCount / $previousWeekTotalCount) * 100 : 0;
        $previousWeekNegativePercentage = $previousWeekTotalCount > 0 ? ($previousWeekNegativeCount / $previousWeekTotalCount) * 100 : 0;

        $currentWeekPositivePercentage = $currentWeekTotalCount > 0 ? ($currentWeekPositiveCount / $currentWeekTotalCount) * 100 : 0;
        $currentWeekNegativePercentage = $currentWeekTotalCount > 0 ? ($currentWeekNegativeCount / $currentWeekTotalCount) * 100 : 0;

        $positiveDiff = $currentWeekPositivePercentage - $previousWeekPositivePercentage;
        $negativeDiff = $currentWeekNegativePercentage - $previousWeekNegativePercentage;

        // Round to 0 decimal places
        $positiveDiff = round($positiveDiff, 0);
        $negativeDiff = round($negativeDiff, 0);

        return response()->json([
            'byDays' => $byDays,
            'positiveDiff' => $positiveDiff,
            'negativeDiff' => $negativeDiff,
        ]);
    }

    public function getMonthlyReport(Request $request) {
        $userId = auth()->id();

        // Get the date from the request or default to the current month
        $dateParam = $request->query('date');
        if ($dateParam) {
            // Parse the provided date parameter
            $startOfMonth = Carbon::createFromFormat('m-Y', $dateParam)->startOfMonth();
        } else {
            // Default to the current month
            $startOfMonth = Carbon::now()->startOfMonth();
        }
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Calculate the start and end of the calendar month
        $startOfCalendarMonth = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendarMonth = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Fetch moods for the specified month
        $moods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $calendarMoods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfCalendarMonth, $endOfCalendarMonth])
            ->get();

        // Initialize variables
        $daysInMonth = $endOfMonth->day;
        $byDays = array_fill(0, $daysInMonth, []);
        $calendarByDays = [];
        $currentMonthPositiveCount = 0;
        $currentMonthNegativeCount = 0;

        // Process moods for the current month
        foreach ($moods as $mood) {
            $dayIndex = Carbon::parse($mood->created_at)->day - 1;
            $byDays[$dayIndex][] = $mood->value;

            if ($mood->value > 55) {
                $currentMonthPositiveCount++;
            } elseif ($mood->value < 45) {
                $currentMonthNegativeCount++;
            }
        }

        for ($date = $startOfCalendarMonth->copy(); $date->lte($endOfCalendarMonth); $date->addDay()) {
            $dateString = $date->format('d-m-Y');
            $dayMoods = $calendarMoods->filter(function($mood) use ($date) {
                return $mood->created_at->isSameDay($date);
            })->pluck('value');

            $averageMood = $dayMoods->count() > 0 ? $dayMoods->avg() : -1;
            $byCalendarDays[$dateString] = $averageMood;
        }

        $currentMonthTotalCount = $currentMonthPositiveCount + $currentMonthNegativeCount;

        // Fetch moods for the previous month
        $previousMonthStart = $startOfMonth->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $startOfMonth->copy()->subMonth()->endOfMonth();

        $previousMonthMoods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->get();

        $previousMonthPositiveCount = 0;
        $previousMonthNegativeCount = 0;

        // Process moods for the previous month
        foreach ($previousMonthMoods as $mood) {
            if ($mood->value > 55) {
                $previousMonthPositiveCount++;
            } elseif ($mood->value < 45) {
                $previousMonthNegativeCount++;
            }
        }

        $previousMonthTotalCount = $previousMonthPositiveCount + $previousMonthNegativeCount;

        // Calculate percentages
        $previousMonthPositivePercentage = $previousMonthTotalCount > 0 ? ($previousMonthPositiveCount / $previousMonthTotalCount) * 100 : 0;
        $previousMonthNegativePercentage = $previousMonthTotalCount > 0 ? ($previousMonthNegativeCount / $previousMonthTotalCount) * 100 : 0;

        $currentMonthPositivePercentage = $currentMonthTotalCount > 0 ? ($currentMonthPositiveCount / $currentMonthTotalCount) * 100 : 0;
        $currentMonthNegativePercentage = $currentMonthTotalCount > 0 ? ($currentMonthNegativeCount / $currentMonthTotalCount) * 100 : 0;

        // Calculate differences
        $positiveDiff = $currentMonthPositivePercentage - $previousMonthPositivePercentage;
        $negativeDiff = $currentMonthNegativePercentage - $previousMonthNegativePercentage;

        // Round to 0 decimal places
        $positiveDiff = round($positiveDiff, 0);
        $negativeDiff = round($negativeDiff, 0);

        return response()->json([
            'byDays' => $byDays,
            'byCalendarDays' => $byCalendarDays,
            'positiveDiff' => $positiveDiff,
            'negativeDiff' => $negativeDiff,
        ]);
    }

}
