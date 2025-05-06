<?php

namespace App\Http\Controllers;

use App\Models\UserAction;
use Illuminate\Http\Request;

class UserActionController extends Controller
{
    public function recordAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
        ]);

        $action = UserAction::create([
            'user_id' => auth()->id(),
            'action' => $request->action,
        ]);

        return response()->json($action, 201);
    }

    public function getUserActions(Request $request)
    {
        $request->validate([
            'perPage' => 'integer|min:1|max:100',
            'sortBy' => 'in:name,created_at,action',
        ]);

        $query = UserAction::with(['user:id,name,email'])
            ->select('id', 'user_id', 'action', 'created_at', 'updated_at');

        // Apply search filters
            if ($request->filled('search')) {
                $search = $request->input('search');
                $searchBy = $request->input('searchBy', 'user_name');

                switch ($searchBy) {
                    case 'user_name':
                        $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                        break;
                    case 'user_email':
                        $query->whereHas('user', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%");
                        });
                        break;
                    case 'action':
                        $query->where('action', 'like', "%{$search}%");
                        break;
                }
            }

        // Apply sorting
if ($request->sortBy === 'name') {

        $query->select('user_actions.id', 'user_actions.user_id', 'user_actions.action',
                       'user_actions.created_at', 'user_actions.updated_at', 'users.name')
              ->join('users', 'users.id', '=', 'user_actions.user_id')
              ->orderBy('users.name');
    } else {

        $query->select('id', 'user_id', 'action', 'created_at', 'updated_at');

        switch ($request->sortBy) {
            case 'created_at':
                $query->orderBy('created_at', 'desc');
                break;
            case 'action':
                $query->orderBy('action');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
    }


        $perPage = $request->input('perPage', 20);

        return $query->paginate($perPage);
    }
}
