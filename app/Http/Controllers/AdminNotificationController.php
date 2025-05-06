<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\AdminNotificationSetting;
use App\Mail\AdminNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AdminNotificationController extends Controller
{
    /**
     *
     */
    public function index()
    {
        $adminId = Auth::id();
        $notifications = AdminNotification::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     *
     */
    public function unread()
    {
        $adminId = Auth::id();
        $notifications = AdminNotification::where('admin_id', $adminId)
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     *
     */
    public function markAsRead($id)
    {
        $adminId = Auth::id();
        $notification = AdminNotification::where('id', $id)
            ->where('admin_id', $adminId)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json($notification);
    }

    /**
     *
     */
    public function markAllAsRead()
    {
        $adminId = Auth::id();
        AdminNotification::where('admin_id', $adminId)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     *
     *
     */
    public static function createNotification($adminId, $type, $subtype, $title, $content)
        {
            // First check for personal settings
            $settings = AdminNotificationSetting::where('admin_id', $adminId)
                ->where('is_global', false)
                ->first();

            // If no personal settings, check for global settings
            if (!$settings) {
                $settings = AdminNotificationSetting::where('is_global', true)->first();
            }

            // If no settings found or notifications disabled, return null
            if (!$settings || !$settings->notifications_enabled) {
                return null;
            }

            $settingField = self::getSettingFieldBySubtype($subtype);
            if ($settingField && !$settings->$settingField) {
                return null;
            }

            $notification = AdminNotification::create([
                'admin_id' => $adminId,
                'type' => $type,
                'subtype' => $subtype,
                'title' => $title,
                'content' => $content,
                'read' => false,
            ]);

            if ($settings->notification_frequency === 'instant' && $settings->delivery_method === 'email') {
                $admin = Admin::find($adminId);
                if ($admin) {
                    Mail::to($admin->email)->send(new AdminNotificationMail($notification));
                }
            }

            return $notification;
        }



    /**
     *
     */
    private static function getSettingFieldBySubtype($subtype)
    {
        $mapping = [
            'critical_error' => 'critical_system_errors',
            'platform_update' => 'platform_updates',
            'change_history' => 'change_history_audit',
            'new_user' => 'new_user_registration',
            'permission_change' => 'permission_role_changes',
            'failed_login' => 'failed_login_attempts',
            'user_comment' => 'user_comments_feedback',
            'view_analytics' => 'view_user_analytics',
            'export_data' => 'export_user_data',
            'delete_data' => 'delete_user_data',
            'api_alert' => 'api_alerts_webhooks',
            'activity_log' => 'activity_log',
            'automatic_retry' => 'automatic_retries',
            'external_service' => 'connect_external_services',
        ];

        return $mapping[$subtype] ?? null;
    }
}