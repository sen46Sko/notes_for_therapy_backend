<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserSymptom;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserSymptomController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $yearStart = $now->copy()->startOfYear();

        $weekSymptoms = $this->getUserSymptoms($user, $weekStart, $now);
        $monthSymptoms = $this->getUserSymptoms($user, $monthStart, $now);
        $yearSymptoms = $this->getUserSymptoms($user, $yearStart, $now);
        $totalSymptoms = $this->getTotalSymptoms($user);

        return response()->json([
            'week' => $weekSymptoms,
            'month' => $monthSymptoms,
            'year' => $yearSymptoms,
            'total_symptoms' => $totalSymptoms,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'symptom_id' => 'required|exists:symptoms,id',
            'intensity' => 'required|in:mild,moderate,intense,acute',
            'note' => 'nullable|string',
        ]);

        $userSymptom = $request->user()->userSymptoms()->create($validatedData);

        return response()->json($userSymptom, 201);
    }

    private function getUserSymptoms($user, $start, $end)
    {
        return $user->userSymptoms()
            ->with('symptom')
            ->whereBetween('date', [$start, $end])
            ->get();
    }

    private function getTotalSymptoms($user)
    {
        return $user->userSymptoms()
            ->with('symptom')
            ->get()
            ->groupBy('symptom.name')
            ->map(function ($group) {
                $symptom = $group->first()->symptom;
                return [
                    'name' => $symptom->name,
                    'color' => $symptom->color,
                    'count' => $group->count(),
                ];
            })
            ->values();
    }
}
