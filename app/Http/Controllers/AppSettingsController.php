<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppSettingsController extends Controller
{
    /**
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings()
    {
        $settings = AppSetting::getCurrentSettings();

         if ($settings->logo) {
             $settings->logo = asset('storage/' . $settings->logo);
         }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     *
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Role super admin is required'
            ], 403);
        }

        $request->validate([
            'color' => 'required|string|max:7',
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:20480', // 20MB max
        ]);

        $settings = AppSetting::getCurrentSettings();

        $settings->color = $request->color;
        $settings->name = $request->name;

        if ($request->hasFile('logo')) {

            if ($settings->logo && Storage::exists($settings->logo)) {
                Storage::delete($settings->logo);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $settings->logo = $path;
        }

        $settings->save();

        if ($settings->logo) {
            $settings->logo = asset('storage/' . $settings->logo);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings were successfully updated',
            'data' => $settings
        ]);
    }
}