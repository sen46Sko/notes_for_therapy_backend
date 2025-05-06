<?php

namespace App\Http\Controllers;

use App\Models\AdminNotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationSettingController extends Controller
{
    /**
     *
     */
    public function index()
    {
        $adminId = Auth::id();
        $settings = AdminNotificationSetting::where('admin_id', $adminId)->first();

        if (!$settings) {
            $settings = AdminNotificationSetting::create([
                'admin_id' => $adminId,
            ]);
        }

        return response()->json($settings);
    }

    /**
     *
     */
    public function update(Request $request)
    {
        $adminId = Auth::id();
        $settings = AdminNotificationSetting::where('admin_id', $adminId)->first();

        if (!$settings) {
            $settings = new AdminNotificationSetting();
            $settings->admin_id = $adminId;
        }

        if ($request->has('notifications_enabled')) {
            $settings->notifications_enabled = $request->notifications_enabled;
        }

        if ($request->has('notification_frequency')) {
            $settings->notification_frequency = $request->notification_frequency;
        }

        if ($request->has('delivery_method')) {
            $settings->delivery_method = $request->delivery_method;
        }

        if ($request->has('critical_system_errors')) {
            $settings->critical_system_errors = $request->critical_system_errors;
        }

        if ($request->has('platform_updates')) {
            $settings->platform_updates = $request->platform_updates;
        }

        if ($request->has('change_history_audit')) {
            $settings->change_history_audit = $request->change_history_audit;
        }

        if ($request->has('new_user_registration')) {
            $settings->new_user_registration = $request->new_user_registration;
        }

        if ($request->has('permission_role_changes')) {
            $settings->permission_role_changes = $request->permission_role_changes;
        }

        if ($request->has('failed_login_attempts')) {
            $settings->failed_login_attempts = $request->failed_login_attempts;
        }

        if ($request->has('user_comments_feedback')) {
            $settings->user_comments_feedback = $request->user_comments_feedback;
        }

        if ($request->has('view_user_analytics')) {
            $settings->view_user_analytics = $request->view_user_analytics;
        }

        if ($request->has('export_user_data')) {
            $settings->export_user_data = $request->export_user_data;
        }

        if ($request->has('delete_user_data')) {
            $settings->delete_user_data = $request->delete_user_data;
        }

        if ($request->has('api_alerts_webhooks')) {
            $settings->api_alerts_webhooks = $request->api_alerts_webhooks;
        }

        if ($request->has('activity_log')) {
            $settings->activity_log = $request->activity_log;
        }

        if ($request->has('automatic_retries')) {
            $settings->automatic_retries = $request->automatic_retries;
        }

        if ($request->has('connect_external_services')) {
            $settings->connect_external_services = $request->connect_external_services;
        }

        $settings->save();

        return response()->json($settings);
    }

    /**
     * Get global notification settings
     */
    public function getGlobalSettings()
    {
        // Check if admin has permission to view settings
        if (!Auth::user()->rights->modify_permissions) {
            return response()->json([
                'message' => 'You do not have permission to view global notification settings'
            ], 403);
        }

        $settings = AdminNotificationSetting::where('is_global', true)->first();

        if (!$settings) {
            // Get the first admin as a reference for global settings
            $adminId = Auth::id(); // Current admin ID

            // Create default global settings if they don't exist
            $settings = AdminNotificationSetting::create([
                'admin_id' => $adminId,
                'is_global' => true,
                'notifications_enabled' => true,
                'notification_frequency' => 'instant',
                'delivery_method' => 'email',
                // Enable all notification types by default
                'critical_system_errors' => true,
                'platform_updates' => true,
                'change_history_audit' => true,
                'new_user_registration' => true,
                'permission_role_changes' => true,
                'failed_login_attempts' => true,
                'user_comments_feedback' => true,
                'view_user_analytics' => true,
                'export_user_data' => true,
                'delete_user_data' => true,
                'api_alerts_webhooks' => true,
                'activity_log' => true,
                'automatic_retries' => true,
                'connect_external_services' => true,
            ]);
        }

        return response()->json($settings);
    }

    /**
     * Update global notification settings
     */
    public function updateGlobalSettings(Request $request)
    {
        // Check if admin has permission to modify settings
        if (!Auth::user()->rights->modify_permissions) {
            return response()->json([
                'message' => 'You do not have permission to modify global notification settings'
            ], 403);
        }

        $settings = AdminNotificationSetting::where('is_global', true)->first();

        if (!$settings) {
            $settings = new AdminNotificationSetting();
            $settings->admin_id = Auth::id(); // Current admin ID
            $settings->is_global = true;
        }

        // Update fields similar to the existing update() method
        if ($request->has('notifications_enabled')) {
            $settings->notifications_enabled = $request->notifications_enabled;
        }

        if ($request->has('notification_frequency')) {
            $settings->notification_frequency = $request->notification_frequency;
        }

        if ($request->has('delivery_method')) {
            $settings->delivery_method = $request->delivery_method;
        }

        if ($request->has('critical_system_errors')) {
            $settings->critical_system_errors = $request->critical_system_errors;
        }

        if ($request->has('platform_updates')) {
            $settings->platform_updates = $request->platform_updates;
        }

        if ($request->has('change_history_audit')) {
            $settings->change_history_audit = $request->change_history_audit;
        }

        if ($request->has('new_user_registration')) {
            $settings->new_user_registration = $request->new_user_registration;
        }

        if ($request->has('permission_role_changes')) {
            $settings->permission_role_changes = $request->permission_role_changes;
        }

        if ($request->has('failed_login_attempts')) {
            $settings->failed_login_attempts = $request->failed_login_attempts;
        }

        if ($request->has('user_comments_feedback')) {
            $settings->user_comments_feedback = $request->user_comments_feedback;
        }

        if ($request->has('view_user_analytics')) {
            $settings->view_user_analytics = $request->view_user_analytics;
        }

        if ($request->has('export_user_data')) {
            $settings->export_user_data = $request->export_user_data;
        }

        if ($request->has('delete_user_data')) {
            $settings->delete_user_data = $request->delete_user_data;
        }

        if ($request->has('api_alerts_webhooks')) {
            $settings->api_alerts_webhooks = $request->api_alerts_webhooks;
        }

        if ($request->has('activity_log')) {
            $settings->activity_log = $request->activity_log;
        }

        if ($request->has('automatic_retries')) {
            $settings->automatic_retries = $request->automatic_retries;
        }

        if ($request->has('connect_external_services')) {
            $settings->connect_external_services = $request->connect_external_services;
        }

        $settings->save();

        return response()->json($settings);
    }
}