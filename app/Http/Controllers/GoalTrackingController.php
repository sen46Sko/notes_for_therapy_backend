<?php

namespace App\Http\Controllers;

use App\Models\GoalTracking;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use DB;
use Carbon\Carbon;
class GoalTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {

        switch ($request->tabValue) {
            case 'today':
                $data = GoalTracking::with(['goal' => function ($query) {
                    $query->select('id', 'title');
                }])
                    ->where('user_id', Auth::user()->id)
                    ->whereDate('date', Carbon::today())
                    ->where('goal_id', $id)->get();


                break;
            case 'thismonth':
                // print_r(Carbon::now()->month());
                // die;
                $data = GoalTracking::select('rating', 'date')->with(['goal' => function ($query) {
                    $query->select('id', 'title');
                }])
                    ->where('user_id', Auth::user()->id)
                    // ->whereMonth('date', Carbon::now()->month())

                    ->where(DB::raw('month(date)'), '=', $request->month)
                    ->where('goal_id', $id)->get();
                break;


            case 'year':
                $data = GoalTracking::select('rating', 'date', 'comment')
                    ->with(['goal' => function ($query) {
                        $query->select('id', 'title');
                    }])
                    ->where('user_id', Auth::user()->id)
                    ->where(DB::raw('YEAR(date)'), '=', $request->year)
                    ->where('goal_id', $id)->get()->toArray();

                $x = array();


                foreach ($data as $dat) {
                    $dat['month'] = Carbon::parse($dat['date'])->format('F');
                    $x['month'][$dat['month']][] = $dat['rating'];
                    $x['comment'][] = $dat['comment'];
                    // $x[$dat['comment']][] = $dat['comment'];
                }

                $final = array();
                $previous = [];

                foreach ($x['month'] as $key => $d) {
                    $count = count($d);
                    if ($d != '' || $d != 0) {
                        $previous[] = $d;
                        $final[] = array('month' => $key, 'rating' => array_sum($d) / $count, 'comment' => $x['comment']);
                    } else {
                        $final[] = array('month' => $key, 'rating' => array_sum($previous) / $count, 'comment' => $x['comment']);
                    }
                }


                return $final;
                break;
            case 'yearwithmonth':

                $data = GoalTracking::with(['goal' => function ($query) {
                    $query->select('id', 'title');
                }])
                    ->where('user_id', Auth::user()->id)
                    ->whereMonth('date', $request->month)->whereYear('date', $request->year)
                    ->where('goal_id', $id)->get();

                $getdateandrating = array();
                foreach ($data as $date) {
                    $getdateandrating[date('d', strtotime($date->date))] = $date->rating;
                    $getdateandrating['comment'][date('d', strtotime($date->date))] = $date->comment;
                }
                $arr = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'Deccember');
                $month = $arr[$request->month];
                $year = $request->year;

                $first_day_this_month2010 = date('01-m-Y 00:00:00', strtotime("$month $year"));
                $last_day_april_2010 = date('t-m-Y 12:59:59', strtotime("$month $year"));

                //                    return array($first_day_this_month2010,$last_day_april_2010);
                $start = new \DateTime("$first_day_this_month2010");
                $end = new \DateTime("$last_day_april_2010");

                $interval = new \DateInterval('P1D');
                $dateRange = new \DatePeriod($start, $interval, $end);

                $weekNumber = 1;
                $weeks = array();
                foreach ($dateRange as $date) {
                    $weeks[$weekNumber][] = $date->format('d');
                    if ($date->format('w') == 6) {
                        $weekNumber++;
                    }
                }

                $finalweek = array();
                $previous = 0;
                $days = array();
                foreach ($weeks as $key => $we) {

                    $week = array();

                    foreach ($we as $key => $we2) {
                        //                           echo '<pre>'; print_r($we2);
                        if (@$getdateandrating[$we2] != '') {
                            $previous  = @$getdateandrating[$we2];
                            $week[$we2]['rating'] = @$getdateandrating[$we2];
                            $week[$we2]['comment'] = @$getdateandrating['comment'][$we2];
                        } else {
                            $week[$we2]['rating'] = $previous;
                            $week[$we2]['comment'] = @$getdateandrating['comment'][$we2];
                        }
                    }

                    $days[] = $week;

                    $finalweek[] = ceil(array_sum($week) / count($week));


                }
                $pre = 0;
                $wekss = array();
                foreach ($finalweek as $key => $we2) {
                    //                           echo '<pre>'; print_r($we2);

                    $keys = $key + 1;


                    if (@$we2 != 0) {
                        $pre = $we2;
                        $wekss[] = array('week' => $keys, 'rating' => $we2, 'daily' => $days[$key]['comment']);
                    } else {
                        $wekss[] = array('week' => $keys, 'rating' => $pre, 'daily' => $days[$key]);
                    }
                }


                return $wekss;
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data
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

        $date = new \DateTime("now", new \DateTimeZone($request->timezone));
        // Write artisan command to create a new migration
        // php artisan make:migration change_int_to_string_subscriptions_subscription_id
        foreach ($request->goal_id as $key => $value) {
            if (isset($request->comments[$key])) {
                GoalTracking::create([
                    'user_id' => Auth::user()->id, 'goal_id' => $key, 'rating' => $value, 'date' => $date->format('Y-m-d H:i:s'), 'comment' => $request->comments[$key]
                ]);
            }
        }
        return response()->json([
            'success' => true,
            'message' => "Added tracking information"
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
