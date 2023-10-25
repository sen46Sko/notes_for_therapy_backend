<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Auth\Events\Validated;
use Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $note = Note::with('tracking')->where('user_id',Auth::user()->id)->get();
        return response()->json([
            'success' => true,
            'data' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $note = Note::create($request->all()+['user_id'=>Auth::user()->id]);
        return response()->json([
            'success' => true,
            'data' => $note->id
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Note $note)
    {
        return response()->json([
            'success' => true,
            'data' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Note::find($id)->update($request->all());
        $note = Note::find($id);
        return response()->json([
            'success' => true,
            'data' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Note::find($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Delete Successfully"
        ], Response::HTTP_OK);
    }
}
