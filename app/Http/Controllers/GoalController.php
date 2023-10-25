<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Note;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $goal =Goal::where('user_id',Auth::user()->id)->get();
        return response()->json([
            'success' => true,
            'data' => $goal
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

        if (Goal::where(['title'=>$request->title,'user_id'=>Auth::user()->id])->exists()) {
            $message = [
                'message' => "Goal has already been entered"
            ];
            return response()->json($message,500);
        }
        $goal =Goal::create($request->all()+['user_id'=>Auth::user()->id]);
        return response()->json([
            'success' => true,
            'data' => $goal
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
    public function update(Request $request, Goal $goal)
    {
        $goal->fill($request->post())->save();
        return response()->json([
            'success' => true,
            'data' => $goal
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( Goal $goal)
    {
        try {
            $goal->delete();
            $message ="Deleted Successfully";
        }

//catch exception
        catch(\Exception $e) {
            $message ="Goal Already use in Notes";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ], Response::HTTP_OK);
    }
}
