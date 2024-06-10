<?php

namespace App\Http\Controllers;

use App\Helper\StorageHelper;
use Illuminate\Http\Request;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Models\HomeworkModel;
use Auth;

class HomeWorkController extends Controller
{
    private function storeFile($request) {
        return (new StorageHelper($request->user()->id, 'homework'))->storeFile($request->file('file'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $home = HomeworkModel::where('user_id', Auth::user()->id)->get();
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
        $data = $request->only('short_description', 'description', 'thoughts');

        if ($request->hasFile('file')) {
            // $path = $request->file('file')->store('homework');
            // $data['file']=$path;
            $path = $this->storeFile($request);
            if ($path == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Upload Failed',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $data['file'] = $path;
        }


        $home = HomeworkModel::create($data + ['user_id' => Auth::user()->id]);
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
        $data = $request->except('file');
        if ($request->hasfile('file')) {
            // $path = $request->file('file')->store('homework');
            // $file = $request->file('file');
            $path = $this->storeFile($request);
            if ($path == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Upload Failed',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $data['file'] = $path;
        }
        // $updatedHomework = HomeworkModel::where('id',$id)->update($data);
        $success = HomeworkModel::where('id', $id)->update($data);
        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Update Failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $updatedHomework = HomeworkModel::find($id);
        return response()->json([
            'success' => true,
            'message' => 'Update Successfully',
            'data' => $updatedHomework
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
