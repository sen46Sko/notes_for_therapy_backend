<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteQuestion;
use App\Services\SystemActionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NoteController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    protected SystemActionService $systemActionService;

    public function __construct(SystemActionService $systemActionService)
    {
        $this->systemActionService = $systemActionService;
    }

    public function index(Request $request)
    {
        $query = $request->query('query');

        $q = Note::with('question')
            ->where('user_id', Auth::id());

        if ($query != null && $query != '') {
            $q = $q->where(function ($q) use ($query) {
                    return $q->where('title', 'like', "%{$query}%")
                    ->orWhere('note', 'like', "%{$query}%");
                });
        }

        $notes = $q->orderBy('created_at', 'desc')
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

        $sql = Note::with('question')
            ->where('user_id', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('note', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->toSql();

        Log::info($sql);

        $this->systemActionService->logAction(SystemActionType::NOTES_INTERACTION, [
            'user_id' => auth()->id()
        ]);


        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'question_id' => 'sometimes|exists:note_questions,id',
            'note' => 'required|string',
        ]);

        $validatedData['user_id'] = Auth::id();

        $note = Note::create($validatedData);
        $note->load('question');

        $this->systemActionService->logAction(SystemActionType::NOTES_INTERACTION, [
            'user_id' => auth()->id()
        ]);

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

        $this->systemActionService->logAction(SystemActionType::NOTES_INTERACTION, [
            'user_id' => auth()->id()
        ]);

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

        $this->systemActionService->logAction(SystemActionType::NOTES_INTERACTION, [
            'user_id' => auth()->id()
        ]);

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

        $this->systemActionService->logAction(SystemActionType::NOTES_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'data' => $latestNotes,
            'total' => $totalNotes,
        ]);
    }
}
