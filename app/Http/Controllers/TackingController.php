<?php

namespace App\Http\Controllers;

use App\Models\Tracking;
use Illuminate\Auth\Events\Validated;
use Auth;
use \Carbon\Carbon;
use DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tracking = Tracking::where('user_id', Auth::user()->id)->get();

        return response()->json([
            'success' => true,
            'data' => $tracking
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
        if (!isset($data['goal_id']) || !isset($data['goal_rating'])) {
            $data['goal_id'] = null;
            $data['goal_rating'] = null;
        }
        if (!isset($data['question_id']) || !isset($data['answer'])) {
            $data['question_id'] = null;
            $data['answer'] = null;
        }
        $track = Tracking::create($data + ['user_id' => Auth::user()->id]);

        return response()->json([
            'success' => true,
            'data' => $track
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
        if (!isset($data['goal_id']) || !isset($data['goal_rating'])) {
            $data['goal_id'] = null;
            $data['goal_rating'] = null;
        }
        if (!isset($data['question_id']) || !isset($data['answer'])) {
            $data['question_id'] = null;
            $data['answer'] = null;
        }
        Tracking::find($id)->update($data);
        $tracking = Tracking::find($id);
        return response()->json([
            'success' => true,
            'data' => $tracking
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
        Tracking::find($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Deleted Successfully"
        ], Response::HTTP_OK);
    }

    public function tracking_rating_word(Request $request)
    {

        $year = isset($request->year) ? $request->year : date('Y'); // ternary operator

        $data = Tracking::select('created_at', "weekly_rating", 'previous_week_description', DB::raw('WEEK(created_at) as week'))->where('user_id', Auth::user()->id)->whereYear('created_at', '=', $year)->orderBy('created_at', 'ASC')->get();

        $getdateandrating = array();
        foreach ($data as $key => $date) {
            $checkAvail = $this->myArrayContainsWeek($getdateandrating, $date->week);

            if (strval($checkAvail) != 'err' && $checkAvail >= 0) {
                // dd($getdateandrating);
                $getdateandrating[$checkAvail]['week'] = $date->week;
                $getdateandrating[$checkAvail]['date'] = date('Y-m-d', strtotime($date->created_at));
                $getdateandrating[$checkAvail]['previous_week_description'] = $date->previous_week_description;
                $getdateandrating[$checkAvail]['weekly_rating'] = $date->weekly_rating;
            } else {
                $singleArr = [];
                $singleArr['week'] = $date->week;
                $singleArr['date'] = date('Y-m-d', strtotime($date->created_at));
                $singleArr['previous_week_description'] = $date->previous_week_description;
                $singleArr['weekly_rating'] = $date->weekly_rating;
                array_push($getdateandrating, $singleArr);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $getdateandrating
        ], Response::HTTP_OK);
    }


    function myArrayContainsWeek(array $myArray, $week)
    {

        foreach ($myArray as $key => $element) {
            if ($element['week'] == $week) {
                // echo "key::::$key    week --->> $week";
                // echo ' || ';
                return  intval($key);
            }
        }
        return 'err';
    }
}
