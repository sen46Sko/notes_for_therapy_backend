<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Notification;
use Symfony\Component\HttpFoundation\Response;
class NotificationControler extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($date,$time)
    {
//        print_r(array($date,$time));die('herer');
//      $notification = Notification::whereDate('date','<=',Carbon::now()->subDays(7))->where('user_id',\Auth::user()->id)->get();
      $notification = Notification::where('user_id',\Auth::user()->id)->get();
       return \response()->json(['status'=>true,'data'=>$notification]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Notification::create($request->all()+['user_id'=>\Auth::user()->id]);
        return \response()->json(['status'=>true,'message'=>'Notification Created Successfully']);
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
        Notification::find($id)->delete();
        return \response()->json(['status'=>true,'message'=>'Deleted Successfully']);
    }
}
