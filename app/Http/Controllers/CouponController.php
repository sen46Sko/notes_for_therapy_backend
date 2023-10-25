<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\User;
use App\Models\UserCoupon;
use Yajra\DataTables\DataTables;
use function React\Promise\reduce;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::paginate(10);
        return view('admin.coupon.list',compact('coupons'));
    }
    public function getUsers(Request $request)
    {
        
        if ($request->ajax()) {

            $assignedUsers = UserCoupon::where('coupon_id', $request->coupon_id)->pluck('user_id')->toArray();

            $users = User::whereNotIn('id', $assignedUsers)->get();
            return DataTables::of($users)
                ->addColumn('action', function ($user) {
                    // Add any additional columns or customizations here
                    return '<input type="checkbox" name="user[]" value="' . $user->id . '">';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('users.index');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.coupon.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the form input
        $request->validate([
            'code' => 'required|unique:coupons',
            'days' => 'required|numeric',
            'expiration' => 'nullable|date',
        ]);

        // Create a new coupon
        $coupon = new Coupon();
        $coupon->code = $request->input('code');
        $coupon->days = $request->input('days');
        $coupon->expiration = $request->input('expiration');
        $coupon->save();

        // Redirect to a success page or perform any additional actions
        return redirect()->route('coupons.create')->with('success', 'Coupon created successfully.');
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

        $coupon=  Coupon::find($id);
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success','Deleted Successfully');
    }

    public function assign(Request $request){
        foreach ($request->user as $user){
            UserCoupon::create(['coupon_id'=>$request->id,'user_id'=>$user]);
        }
        return redirect()->route('coupons.index')->with('success','Assigned Successfully');

    }

}
