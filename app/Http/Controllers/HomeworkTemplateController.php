<?php

namespace App\Http\Controllers;

use App\Models\HomeworkTemplate;
use Illuminate\Http\Request;

class HomeworkTemplateController extends Controller
{
    public function index()
    {
        $templates = HomeworkTemplate::where('user_id', auth()->id())->get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $request['user_id'] = auth()->id();

        $template = HomeworkTemplate::create($request->all());

        return response()->json($template, 201);
    }

    public function show($id)
    {
        $template = HomeworkTemplate::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        return response()->json($template);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string',
        ]);

        $template = HomeworkTemplate::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $template->update($request->all());

        return response()->json($template);
    }

    public function destroy($id)
    {
        $template = HomeworkTemplate::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $template->delete();

        return response()->json(null, 204);
    }
}

