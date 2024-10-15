<?php

namespace App\Http\Controllers;

use App\Models\UserNotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationSettingController extends Controller
{
    public function update(Request $request, $id)
    {
        $setting = UserNotificationSetting::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'show_notifications' => 'sometimes|boolean',
            'sound' => 'sometimes|boolean',
            'preview' => 'sometimes|boolean',
            'mail' => 'sometimes|boolean',
            'marketing_ads' => 'sometimes|boolean',
            'reminders' => 'sometimes|boolean',
            'mood' => 'sometimes|boolean',
            'notes' => 'sometimes|boolean',
            'symptoms' => 'sometimes|boolean',
            'goals' => 'sometimes|boolean',
            'homework' => 'sometimes|boolean',
        ]);

        $setting->update($validated);
        return response()->json($setting);
    }
}
