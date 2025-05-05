<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminDevice;
use App\Models\AdminActivityLog;
use App\Models\FailedLoginAttempt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class DeviceManagementController extends Controller
{
    public function getDevices()
    {
        try {
            $devices = AdminDevice::with('admin:id,name')
                ->orderBy('last_active_at', 'desc')
                ->get();

            return response()->json($devices);
        } catch (\Exception $e) {
            Log::error('Failed to get devices: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to get devices'], 500);
        }
    }

    public function getActivityLogs()
    {
        try {
            $logs = AdminActivityLog::with('admin:id,name')
                ->orderBy('created_at', 'desc')
                ->take(100)
                ->get();

            return response()->json($logs);
        } catch (\Exception $e) {
            Log::error('Failed to get activity logs: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to get activity logs'], 500);
        }
    }

    public function getFailedLoginAttempts()
    {
        try {
            $attempts = FailedLoginAttempt::orderBy('attempted_at', 'desc')
                ->take(100)
                ->get();

            return response()->json($attempts);
        } catch (\Exception $e) {
            Log::error('Failed to get failed login attempts: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to get failed login attempts'], 500);
        }
    }

    public function blockDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:admin_devices,id'
        ]);

        try {
            $device = AdminDevice::findOrFail($request->device_id);
            $device->is_blocked = true;
            $device->save();

            $this->logActivity('block_device', "Blocked device: {$device->device_name} ({$device->ip_address})");

            return response()->json(['message' => 'Device blocked successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to block device: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to block device'], 500);
        }
    }

    public function unblockDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:admin_devices,id'
        ]);

        try {
            $device = AdminDevice::findOrFail($request->device_id);
            $device->is_blocked = false;
            $device->save();

            $this->logActivity('unblock_device', "Unblocked device: {$device->device_name} ({$device->ip_address})");

            return response()->json(['message' => 'Device unblocked successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to unblock device: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to unblock device'], 500);
        }
    }

    public function forceLogout(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:admin_devices,id'
        ]);

        try {
            $device = AdminDevice::findOrFail($request->device_id);
            $device->is_active = false;
            $device->save();


            $this->logActivity('force_logout', "Force logout device: {$device->device_name} ({$device->ip_address})");

            return response()->json(['message' => 'Device logged out successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to force logout: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to force logout'], 500);
        }
    }

    protected function logActivity($action, $description = null)
    {
        $agent = new Agent();
        $admin = Auth::user();

        AdminActivityLog::create([
            'admin_id' => $admin->id,
            'device_type' => $this->getDeviceType($agent),
            'ip_address' => request()->ip(),
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }

    protected function getDeviceType($agent)
    {
        if ($agent->isDesktop()) {
            return 'pc';
        } elseif ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        }
        return 'pc';
    }
}