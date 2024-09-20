<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserNotificationSettingController extends Controller
{
    public function update(Request $request, NotificationSetting $setting)
    {
        $request->validate([
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

        $setting->update($request->all());
        return response()->json($setting);
    }
}
