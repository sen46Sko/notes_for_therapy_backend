<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\HomeworkModel;
use Auth;
class HomeWorkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $home =HomeworkModel::where('user_id',Auth::user()->id)->get();
        return response()->json([
            'success' => true,
            'data' => $home
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
        $data = $request->only('short_description','description','thoughts');

        if($request->hasFile('file')){
            $path = $request->file('file')->store('homework');
            $data['file']=$path;
        }


        $home =HomeworkModel::create($data +['user_id'=>Auth::user()->id]);
        return response()->json([
            'success' => true,
            'data' => $home
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
        $data= $request->except('file');
        if($request->hasfile('file')) {
            $path = $request->file('file')->store('homework');
            $data['file'] =$path;
        }
        HomeworkModel::where('id',$id)->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Update Successfully'
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($home)
    {

        HomeworkModel::find($home)->delete();
        return response()->json([
            'success' => true,
            'messsage' => "Deleted Successfully"
        ], Response::HTTP_OK);
    }
}
