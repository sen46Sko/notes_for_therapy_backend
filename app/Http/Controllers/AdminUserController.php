<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function users(Request $request) {

        $validated = $request->validate([
            "search" => ["nullable", "string"],
            "sub_plan" => ["nullable", "string"],
            "sub_status" => ["nullable", "string"],
            "gender" => ["nullable", "string"],
            "rows" => ["nullable", "integer", "min:1", "max:50"],
            "page" => ["nullable", "integer", "min:1"]
        ]);


        $search = $validated["search"] ?? null;
        $sub_plan = $validated['sub_plan'] ?? null;
        $sub_status = $validated['sub_status'] ?? null;
        $gender = $validated['gender'] ?? null;
        $rows = $validated['rows'] ?? 50;
        $page = $validated['page'] ?? 1;

        $users = User::join('subscriptions', 'subscriptions.user_id', '=', 'users.id')
        ->select(
            'users.id', 
            'users.name', 
            'users.email', 
            'users.gender', 
            'subscriptions.status as sub_status',
            'subscriptions.provider_subscription_id as sub_plan'
        )
            ->when($gender, function ($q, $gender) {
                return $q->where('gender', $gender);
            })
            ->when($search, function ($q, $search) {
                return $q->whereRaw('MATCH (users.name, users.email) AGAINST (? IN BOOLEAN MODE)', ['+' . $search . '*']);
            })
            ->when($sub_status, function ($q, $sub_status) {
                return $q->where('subscriptions.status', $sub_status);
            })
            ->when($sub_plan, function ($q, $sub_plan) {
                return $q->where('subscriptions.provider_subscription_id', $sub_plan);
            })
            ->distinct('users.id')  
            ->paginate($rows);

       return response()->json($users);
    }
}
