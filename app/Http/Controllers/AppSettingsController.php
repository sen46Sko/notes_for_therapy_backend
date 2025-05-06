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

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function updateSettings(Request $request)
    {

        $settings = AppSetting::getCurrentSettings();

        $settings->color = $request->color;
        $settings->name = $request->name;

        if ($request->hasFile('logo')) {

            if ($settings->getRawOriginal('logo')) {
                Storage::disk(config('filesystems.default'))->delete($settings->getRawOriginal('logo'));
            }


            $path = $request->file('logo')->store('logos', config('filesystems.default'));
            $settings->logo = $path;
        }

        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Settings were successfully updated',
            'data' => $settings
        ]);
    }

}