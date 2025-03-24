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

        // Apply sorting
        switch ($request->sortBy) {
            case 'name':
                $query->join('users', 'users.id', '=', 'user_actions.user_id')
                    ->orderBy('users.name');
                break;
            case 'created_at':
                $query->orderBy('created_at', 'desc');
                break;
            case 'action':
                $query->orderBy('action');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('perPage', 20);

        return $query->paginate($perPage);
    }
}
