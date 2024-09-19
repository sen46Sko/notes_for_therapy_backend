<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = $request->query('query');

        $notes = Note::with('question')
            ->where('user_id', Auth::id())
            ->when($query, function ($q) use ($query) {
                return $q->where('title', 'like', "%{$query}%")
                    ->orWhere('note', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'question' => [
                        'id' => $note->question->id,
                        'title' => $note->question->title,
                    ],
                    'note' => $note->note,
                    'created_at' => $note->created_at->toDateTimeString(),
                ];
            });

        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'question_id' => 'required|exists:note_questions,id',
            'note' => 'required|string',
        ]);

        $validatedData['user_id'] = Auth::id();

        $note = Note::create($validatedData);
        $note->load('question');

        return response()->json([
            'id' => $note->id,
            'title' => $note->title,
            'question' => [
                'id' => $note->question->id,
                'title' => $note->question->title,
            ],
            'note' => $note->note,
            'created_at' => $note->created_at->toDateTimeString(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $note = Note::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'question_id' => 'sometimes|required|exists:note_questions,id',
            'note' => 'sometimes|required|string',
        ]);

        $note->update($validatedData);
        $note->load('question');

        return response()->json([
            'id' => $note->id,
            'title' => $note->title,
            'question' => [
                'id' => $note->question->id,
                'title' => $note->question->title,
            ],
            'note' => $note->note,
            'created_at' => $note->created_at->toDateTimeString(),
            'updated_at' => $note->updated_at->toDateTimeString(),
        ]);
    }

    public function destroy($id)
    {
        $note = Note::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $note->delete();

        return response()->json(null, 204);
    }

    public function activity()
    {
        $latestNotes = Note::with('question')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'question' => [
                        'id' => $note->question->id,
                        'title' => $note->question->title,
                    ],
                    'note' => $note->note,
                    'created_at' => $note->created_at->toDateTimeString(),
                ];
            });

        $totalNotes = Note::where('user_id', Auth::id())->count();

        return response()->json([
            'data' => $latestNotes,
            'total' => $totalNotes,
        ]);
    }
}
