<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'feedback_type' => 'required|string',
            'feedback' => 'required|string',
            'custom_option' => 'nullable|string|required_if:feedback_type,other',
        ]);

        $feedback = new Feedback();
        $feedback->feedback_type = $validated['feedback_type'];
        $feedback->feedback = $validated['feedback'];
        $feedback->custom_option = $validated['custom_option'] ?? null;

        if (auth()->check()) {
            $feedback->user_id = auth()->id();
        }

        $feedback->save();

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'feedback' => $feedback
        ], 201);
    }

    public function index(Request $request)
    {
        $query = Feedback::with('user')->latest();

        if ($request->has('query') && !empty($request->query('query'))) {
            $searchQuery = $request->query('query');
            $query->where(function($q) use ($searchQuery) {
                $q->where('feedback', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('feedback_type', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('custom_option', 'LIKE', "%{$searchQuery}%")
                  ->orWhereHas('user', function($userQuery) use ($searchQuery) {
                      $userQuery->where('name', 'LIKE', "%{$searchQuery}%");
                  });
            });
        }

         if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {

                    $q->where('id', 'LIKE', "%{$searchTerm}%")

                      ->orWhere('problem_description', 'LIKE', "%{$searchTerm}%")

                      ->orWhere('status', 'LIKE', "%{$searchTerm}%")

                      ->orWhereHas('user', function($q) use ($searchTerm) {
                          $q->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                      })

                      ->orWhereHas('problem', function($q) use ($searchTerm) {
                          $q->where('title', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

        if ($request->has('filters')) {
            $filters = $request->filters;

            if (isset($filters['timePeriod'])) {
                $timePeriod = $filters['timePeriod'];

                if ($timePeriod === 'range' && isset($filters['from']) && isset($filters['to'])) {
                    $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
                } elseif ($timePeriod === 'today') {
                    $query->whereDate('created_at', today());
                } elseif ($timePeriod === 'yesterday') {
                    $query->whereDate('created_at', today()->subDay());
                } elseif ($timePeriod === 'week') {
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                } elseif ($timePeriod === 'month') {
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                }
            }
        }

        $feedback = $query->paginate(15);

        return response()->json($feedback);
    }

    public function show(Feedback $feedback)
    {
        return response()->json($feedback->load('user'));
    }
}