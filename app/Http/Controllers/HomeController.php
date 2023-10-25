<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Symptom;
use App\Models\SymptomTracking;
use App\Models\GoalTracking;
use App\Models\Goal;
use App\Models\Note;
use App\Models\Tracking;
use App\Models\HomeworkModel;
use App\Models\Subscription;
use App\Models\UserCoupon;
use App\Models\UsedCoupon;
use App\Models\Notification;
use App\Models\Follow;
use App\Models\Coupon;
use Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $title= array(
            'title' => 'Home',
            'active' =>'home'
        );
        return view('admin.dashboard',compact('title'));
    }
    public function logout()
    {
        Auth::logout();
        return redirect('login');
    }
    public function index_user()
    {
        $title= array(
            'title' => 'Users',
            'active' =>'users'
        );
        $users = User::latest();
        if (request()->has('search')) {
            $users->where('name', 'Like', '%' . request()->input('search') . '%')
                ->orWhere('email', 'Like', '%' . request()->input('search') . '%')
                ->orWhere('age', 'Like', '%' . request()->input('search') . '%');
        }
        $users = $users->paginate (5)->setPath ( '' );
        $users->appends(array(
            'search'=>request()->input('search')
        ));
        return view('admin.user',compact('users','title'));
    }
    public function Activate($id)
    {
        $user=User::find($id);
        $user->verify_status=1;
        $user->save();
        return redirect()->back()->with('message','User activated successfuly');

    }
    public function Inactivate($id)
    {
        $user=User::find($id);
        $user->verify_status=0;
        $user->save();
        return redirect()->back()->with('message','User Inactivated successfuly');

    }

    public function destroy(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            Tracking::where('user_id', Auth::user()->id)->delete();
            GoalTracking::where('user_id', Auth::user()->id)->delete();
            Goal::where('user_id', Auth::user()->id)->delete();
            Note::where('user_id', Auth::user()->id)->delete();
            Symptom::where('user_id', Auth::user()->id)->delete();
            UsedCoupon::where('user_id', Auth::user()->id)->delete();
            SymptomTracking::where('user_id', Auth::user()->id)->delete();
            HomeworkModel::where('user_id', Auth::user()->id)->delete();
            Notification::where('user_id', Auth::user()->id)->delete();
            User::whereId(Auth::user()->id)->delete();
        } else {
            // Authentication failed...
            return redirect()->back()->withInput()->withErrors(['email' => 'Invalid credentials']);
        }

        return redirect()->back()->with('message','Account Deleted Successfully');
    }
}
