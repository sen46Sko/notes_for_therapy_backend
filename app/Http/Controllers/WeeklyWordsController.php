<?php

namespace App\Http\Controllers;

use App\Models\WeeklyWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WeeklyWordsController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $weeklyWord = WeeklyWord::where('user_id', Auth::user()->id)->get();

        return response()->json([
            'success' => true,
            'data' => $weeklyWord
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
        // Validator::make($request->all(), [
        //     'weekly_rating' => 'required',
        //     'previous_week_description' => 'required',
        // ])->validate();
        $data = $request->all();
        $weeklyWord = WeeklyWord::create($data + ['user_id' => Auth::user()->id]);

        return response()->json([
            'success' => true,
            'data' => $weeklyWord
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $data = $request->all();
        WeeklyWord::find($id)->update($data);
        $weeklyWord = WeeklyWord::find($id);
        return response()->json([
            'success' => true,
            'data' => $weeklyWord
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
        WeeklyWord::find($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Deleted Successfully"
        ], Response::HTTP_OK);
    }

}
