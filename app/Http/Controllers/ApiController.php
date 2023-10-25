<?php

namespace App\Http\Controllers;

use JWTAuth;
use Auth;
use Hash;
use Illuminate\Support\Str;
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
use App\Helper\Helper;
use App\Models\GroupSchedule;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SchedulesExport;
use DB;
use Carbon\Carbon;
use api;
use App\Models\UserType;
use App\Models\TrainerClient;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserEmail;

class ApiController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            // 'phone_number' => 'required|unique:users',
            'password' => 'required|confirmed|string|min:6',


        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message, 500);
        }
        if ($request->has('image')) {
            $path = $request->file('image')->store('user');
        } else {
            $path = "";
        }
        $date =Carbon::now();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'image' => $path,
            'trial_start' => Carbon::now(),
            'trial_end' => $date->addDays(14),
            'subscription_status' => 0,
            // 'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
            'fcm_token' => '',

        ]);




        Mail::to($user->email)->send(new NewUserEmail($user));
        return $this->authenticate($request);
    }


    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'unique:users,email,' . Auth::user()->id
        ]);
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message, 500);
        }
        $data = $request->except('image');
        if ($request->hasfile('image')) {
            $path = $request->file('image')->store('user');
            $data['image'] = $path;
        }
        User::where('id', Auth::user()->id)->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Update Successfully'
        ], Response::HTTP_OK);
    }

    public function ResetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'email' => ['required'],

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message, 500);
        }

        $user = User::where('email', $request->email)->first();

        if (isset($user)) {
            $user->update(['password' => bcrypt($request->password)]);

            return response()->json([
                'status' => True,
                "message" => 'Updated Successfully'
            ]);
        } else {
            return response()->json([
                'status' => True,
                "message" => 'Invalid Email '
            ]);
        }
    }

    public function change_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'currentpassword' => ['required'],

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message, 500);
        }
        if (Hash::check($request->currentpassword, Auth::user()->password)) {
            User::whereId(Auth::user()->id)->update(['password' => bcrypt($request->password)]);
            return response()->json([
                'status' => True,
                "message" => 'Updated Successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Current Password Does Not Match',
            ], 500);
        }
    }

    public function authenticate(Request $request)
    {

        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50',

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message, 500);
        }
        //Request is validated
        //Crean token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',

                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
        }
        if ($request->device_type != "web") {
            $user = User::find(Auth::user()->id);
            $user->fcm_token = @$request->fcm_token;
            $user->save();
        } else {
            $user = Auth::user();
        }
        $trialEndDate = Carbon::parse($user->trial_end);
        if (!$trialEndDate->isFuture()) {
            $user->trial_end =true;
        } else {
            $user->trial_end = false;
        }
        $coupon = UserCoupon::with('coupon')->where('user_id', $user->id)->get();
        foreach ($coupon as $coup){
            if(UsedCoupon::where(['user_id'=>Auth::user()->id,'coupon_id'=>$coup->coupon->id])->exists()){
                $coup->used =true;
            }else{
                $coup->used =false;
            }
        }

        $adminsymptom = User::with('symptom')->where('role', 1)->get();
        $subscription = Subscription::where('user_id', $user->id)->first();
        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
            'user_details' => $user,
            'adminsymptom' => $adminsymptom,
            'subscription' => $subscription,
            'promocode'=>$coupon
        ]);
    }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function forgot(Request $request)
    {

        $credentials = request()->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (isset($user)) {
            $status = Password::sendResetLink($credentials);
            if ($status == Password::RESET_LINK_SENT) {

                return [
                    'status' => true,
                    'message' => __($status)
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Server issue try after some time ',
                ];
            }
            return response()->json([
                'status' => true,
                "message" => 'Reset password link sent on your email address.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                "message" => 'Email Id is not Exist '
            ]);
        }
    }

    public function verifyCode(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $user = User::where('email', $request->email)->first();

        $credentials = request()->validate(['email' => 'required|email']);
        $date = Carbon::now();
        $date = strtotime($date);
        $now = date("Y-m-d H:i:s", $date);

        if ($user->code_expiry < $now) {
            return response()->json([
                'status' => true,
                "message" => 'Verification Code Expired'
            ]);
        } else {
            if ($request->code == $user->verification_code) {
                // Password::sendResetLink($credentials);



                return response()->json([
                    'status' => true,
                    "message" => 'Success.'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    "message" => 'Please enter correct verification code'

                ]);
            }
        }
    }


    public function get_user(Request $request)
    {
        //$user =User::get();
        $user = JWTAuth::authenticate($request->bearerToken());
        $subscription = Subscription::where('user_id', $user->id)->first();
        $coupon = UserCoupon::with('coupon')->where('user_id', $user->id)->get();
        foreach ($coupon as $coup){
            if(UsedCoupon::where(['user_id'=>Auth::user()->id,'coupon_id'=>$coup->coupon->id])->exists()){
                $coup->used =true;
            }else{
                $coup->used =false;
            }
        }
        $adminsymptom = User::with('symptom')->where('role', 1)->get();
        return response()->json([
            "status" => true,
            'user' => $user,
            'subscription' => $subscription,
            'adminsymptom' => $adminsymptom,
            'promocode' => $coupon,
        ]);
    }



    public function socialloginwith()
    {
        $credentials = request(['provider_id', 'password']);
        $email = request('email');
        $devices = request(['device_type', 'device_token', 'fcm_token']);
        if (!$token = JWTAuth::attempt($credentials)) {

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        User::where('email', $email)
            ->update(['provider_id' => $credentials['provider_id'], 'device_type' => $devices['device_type'], 'device_token' => $devices['device_token'], 'fcm_token' => $devices['fcm_token']]);
        return response()->json([
            'token' => $token,
            "status" => 200,
        ]);
    }

    public function socialLogin(Request $request)
    {

        //return $request;
        //dd($request->all());
        if ($user = User::where('provider_id', '=', $request->provider_id)->first()) {
            $email = $user->email;
            $name = $user->name;
            $provider_name = $user->provider_name;
            $provider_id = $request->provider_id;
            $device_type = $user->device_type;
            $device_token = $user->device_token;
            $fcm_token = $user->fcm_token;
            $mobile_number = $user->mobile_number;
        } else {

            $email = $request->email;
            $name = $request->name;
            $provider_name = $request->provider_name;
            $provider_id = $request->provider_id;
            $device_type = $request->device_type;
            $mobile_number = $request->mobile_number;
            $device_token = $request->device_token;
            $fcm_token = $request->fcm_token;
        }

        if (User::where('email', '=', $email)->count() > 0) {

            $ex = User::where('email', $email)->first();

            if ($ex->provider_name == Null) {

                $email = $email;
                $password = Hash::make($request->password);

                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            } elseif (User::where('provider_id', '=', $provider_id)->count() > 0) {

                // $email = $request->email;
                $password = Hash::make($request->password);
                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            } else {
                User::where('email', $email)
                    ->update(['provider_id' => $provider_id, 'provider_name' => $provider_name]);

                $email = $email;
                $password = Hash::make($request->password);

                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            }
        } else {

            if (User::where('provider_id', '=', $provider_id)->count() > 0) {
                $email = $email;
                $password = Hash::make($request->password);
                return $this->socialloginwith($device_type, $device_token, $fcm_token);
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'mobile_number' => $request->mobile_number,
                    'provider_id' => $request->provider_id,
                    'provider_name' => $request->provider_name,
                    'password' => Hash::make($request->password),
                    'device_type' => $request->device_type,
                    'device_token' => $request->device_token,
                    'fcm_token' => $request->fcm_token,
                ]);
                $email = $email;
                $password = $request->password;

                return $this->authenticate($request);
            }
        }
    }
    public function destroy()
    {
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
        return response()->json([
            "status" => 200,
            "message" => "Deleted Successfully",
        ]);
    }
    public function applyCoupon($couponid){

        $coupon =Coupon::find($couponid);
        if(UsedCoupon::where(['user_id'=>Auth::user()->id,'coupon_id'=>$couponid])->exists()){
          $message ='You have already Used';
        }else{
            $user = User::find(Auth::user()->id);
            $user->trial_end = Carbon::now()->addDays($coupon->days);
            $user->save();
            UsedCoupon::create(['user_id'=>Auth::user()->id,'coupon_id'=>$couponid]);
            $message ='Coupon Applied';
        }


        return \response()->json(['status'=>true,'message'=>$message]);
    }

}
