<?php

namespace App\Http\Controllers;

use App\Models\NoteQuestion;

class NoteQuestionController extends Controller
{
    public function index()
    {
        $questions = NoteQuestion::all();
        return response()->json($questions);
    }
}
