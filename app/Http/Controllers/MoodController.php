<?php
// app/Http/Controllers/MoodController.php
namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\Mood;
use App\Models\MoodRelation;
use App\Services\SystemActionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MoodController extends Controller
{
    // private const MOOD_RANGES = [
    //     'Very Sad' => ['min' => 0, 'max' => 10, 'color' => '#FF6B6B'],
    //     'Sad' => ['min' => 11, 'max' => 20, 'color' => '#FF8E72'],
    //     'Worried' => ['min' => 21, 'max' => 30, 'color' => '#FFA07A'],
    //     'Frustrated' => ['min' => 31, 'max' => 44, 'color' => '#FFB347'],
    //     'Neutral' => ['min' => 45, 'max' => 55, 'color' => '#F9DC5C'],
    //     'Pleased' => ['min' => 56, 'max' => 65, 'color' => '#98FB98'],
    //     'Happy' => ['min' => 66, 'max' => 85, 'color' => '#4BB543'],
    //     'Excited' => ['min' => 86, 'max' => 100, 'color' => '#228B22']
    // ];

    protected SystemActionService $systemActionService;

    public function __construct(SystemActionService $systemActionService)
    {
        $this->systemActionService = $systemActionService;
    }

    private const MOOD_RANGES = [
        'Negative' => ['min' => 0, 'max' => 44, 'color' => '#FF6666'],
        'Neutral' => ['min' => 45, 'max' => 55, 'color' => '#668499'],
        'Positive' => ['min' => 56, 'max' => 100, 'color' => '#00CC88']
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|integer|between:0,100',
            'type' => 'required|in:momentary,daily',
            'mood_relation_id' => 'required|exists:mood_relations,id',
            'note' => 'nullable|string',
        ]);

        $mood = Mood::create([
            'value' => $validated['value'],
            'type' => $validated['type'],
            'mood_relation_id' => $validated['mood_relation_id'],
            'note' => $validated['note'],
            'user_id' => auth()->id(),
        ]);

        $this->systemActionService->logAction(SystemActionType::MOODS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json($mood->load('moodRelation'), 201);
    }

    public function destroy($id)
    {
        try {
            $mood = Mood::findOrFail($id);
            if ($mood->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $mood->delete();

            $this->systemActionService->logAction(SystemActionType::MOODS_INTERACTION, [
                'user_id' => auth()->id()
            ]);

            return response()->json(null, 204);
        } catch(\Exception $e){
            return response()->json(['error' => 'Mood not found'], 404);
        }
    }

    public function getMoodByDate(Request $request)
    {
        $date = Carbon::createFromFormat('d-m-Y', $request->query('date'))->startOfDay();
        $moods = Mood::where('user_id', auth()->id())
            ->where('created_at', '>=', $date)
            ->where('created_at', '<', $date->copy()->addDay())
            ->with(['moodRelation'])
            ->get();

        $momentaryMoods = $moods->where('type', 'momentary')->values();

        $this->systemActionService->logAction(SystemActionType::MOODS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json([
            "daily" => $moods->where('type', 'daily')->first(),
            "momentary" => $momentaryMoods,
        ]);
    }

    public function getMoodInfo()
    {
        $relations = MoodRelation::all();

        $this->systemActionService->logAction(SystemActionType::MOODS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'relations' => $relations,
        ]);
    }

    private function calculateMoodCounts($moods)
    {
        $moodCounts = [];
        $totalMoods = count($moods);

        if ($totalMoods === 0) {
            return [];
        }

        // Initialize counters for each mood range
        foreach (self::MOOD_RANGES as $moodName => $range) {
            $moodCounts[$moodName] = 0;
        }

        // Count moods
        foreach ($moods as $mood) {
            foreach (self::MOOD_RANGES as $moodName => $range) {
                if ($mood->value >= $range['min'] && $mood->value <= $range['max']) {
                    $moodCounts[$moodName]++;
                    break;
                }
            }
        }

        // Convert counts to percentages and format response
        $result = [];
        foreach (self::MOOD_RANGES as $moodName => $range) {
            if ($moodCounts[$moodName] > 0) {
                $result[] = [
                    'name' => $moodName,
                    'count' => number_format(($moodCounts[$moodName] / $totalMoods * 100), 0) . '%',
                    'color' => $range['color']
                ];
            }
        }

        $this->systemActionService->logAction(SystemActionType::MOODS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return $result;
    }

    public function getWeeklyReport()
    {
        $userId = auth()->id();
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

        $weekMoodCounts = $this->calculateMoodCounts($moods);

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

        $positiveDiff = round($currentWeekPositivePercentage - $previousWeekPositivePercentage, 0);
        $negativeDiff = round($currentWeekNegativePercentage - $previousWeekNegativePercentage, 0);

        $this->systemActionService->logAction(SystemActionType::MOODS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'byDays' => $byDays,
            'weekMoodCounts' => $weekMoodCounts,
            'positiveDiff' => $positiveDiff,
            'negativeDiff' => $negativeDiff,
        ]);
    }

    public function getMonthlyReport(Request $request)
    {
        $userId = auth()->id();

        $dateParam = $request->query('date');
        if ($dateParam) {
            $startOfMonth = Carbon::createFromFormat('m-Y', $dateParam)->startOfMonth();
        } else {
            $startOfMonth = Carbon::now()->startOfMonth();
        }
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $startOfCalendarMonth = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendarMonth = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $moods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $calendarMoods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfCalendarMonth, $endOfCalendarMonth])
            ->get();

        $daysInMonth = $endOfMonth->day;
        $byDays = array_fill(0, $daysInMonth, []);
        $byCalendarDays = [];
        $currentMonthPositiveCount = 0;
        $currentMonthNegativeCount = 0;

        foreach ($moods as $mood) {
            $dayIndex = Carbon::parse($mood->created_at)->day - 1;
            $byDays[$dayIndex][] = $mood->value;

            if ($mood->value > 55) {
                $currentMonthPositiveCount++;
            } elseif ($mood->value < 45) {
                $currentMonthNegativeCount++;
            }
        }

        $monthMoodCounts = $this->calculateMoodCounts($moods);

        for ($date = $startOfCalendarMonth->copy(); $date->lte($endOfCalendarMonth); $date->addDay()) {
            $dateString = $date->format('d-m-Y');
            $dayMoods = $calendarMoods->filter(function($mood) use ($date) {
                return $mood->created_at->isSameDay($date);
            })->pluck('value');

            $averageMood = $dayMoods->count() > 0 ? $dayMoods->avg() : -1;
            $byCalendarDays[$dateString] = $averageMood;
        }

        $currentMonthTotalCount = $currentMonthPositiveCount + $currentMonthNegativeCount;

        $previousMonthStart = $startOfMonth->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $startOfMonth->copy()->subMonth()->endOfMonth();

        $previousMonthMoods = Mood::where('user_id', $userId)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->get();

        $previousMonthPositiveCount = 0;
        $previousMonthNegativeCount = 0;

        foreach ($previousMonthMoods as $mood) {
            if ($mood->value > 55) {
                $previousMonthPositiveCount++;
            } elseif ($mood->value < 45) {
                $previousMonthNegativeCount++;
            }
        }

        $previousMonthTotalCount = $previousMonthPositiveCount + $previousMonthNegativeCount;

        $previousMonthPositivePercentage = $previousMonthTotalCount > 0 ? ($previousMonthPositiveCount / $previousMonthTotalCount) * 100 : 0;
        $previousMonthNegativePercentage = $previousMonthTotalCount > 0 ? ($previousMonthNegativeCount / $previousMonthTotalCount) * 100 : 0;

        $currentMonthPositivePercentage = $currentMonthTotalCount > 0 ? ($currentMonthPositiveCount / $currentMonthTotalCount) * 100 : 0;
        $currentMonthNegativePercentage = $currentMonthTotalCount > 0 ? ($currentMonthNegativeCount / $currentMonthTotalCount) * 100 : 0;

        $positiveDiff = round($currentMonthPositivePercentage - $previousMonthPositivePercentage, 0);
        $negativeDiff = round($currentMonthNegativePercentage - $previousMonthNegativePercentage, 0);

        $this->systemActionService->logAction(SystemActionType::MOODS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'byDays' => $byDays,
            'byCalendarDays' => $byCalendarDays,
            'monthMoodCounts' => $monthMoodCounts,
            'positiveDiff' => $positiveDiff,
            'negativeDiff' => $negativeDiff,
        ]);
    }
}
