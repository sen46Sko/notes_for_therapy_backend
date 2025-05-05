<?php

namespace App\Services;

use App\Http\Controllers\AdminNotificationController;
use App\Models\User;

class AdminNotificationsEventService
{
    /**
     * Handle new user registration event
     */
    public function handleNewUserRegistration(User $user)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'user_notification',
                'new_user',
                'New User Registration',
                "A new user has registered: {$user->name} ({$user->email})"
            );
        }
    }

    /**
     * Handle user permission change event
     */
    public function handlePermissionChange(User $user, array $oldPermissions, array $newPermissions)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'user_notification',
                'permission_change',
                'User Permission Change',
                "Permissions have been changed for user {$user->name} ({$user->email})"
            );
        }
    }

    /**
     * Handle failed login attempt event
     */
    public function handleFailedLogin($email, $ipAddress)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'user_notification',
                'failed_login',
                'Failed Login Attempt',
                "Failed login attempt for user {$email} from IP address {$ipAddress}"
            );
        }
    }

    /**
     * Handle critical system error event
     */
    public function handleCriticalError($errorMessage, $errorCode)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'system_alert',
                'critical_error',
                'Critical System Error',
                "Critical system error: {$errorMessage} (code: {$errorCode})"
            );
        }
    }

    /**
     * Handle platform update event
     */
    public function handlePlatformUpdate($version, $features)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'system_alert',
                'platform_update',
                "Platform Updated to Version {$version}",
                "The platform has been updated to version {$version}. New features: {$features}"
            );
        }
    }

    /**
     * Handle new user comment event
     */
    public function handleUserComment(User $user, $comment)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'user_notification',
                'user_comment',
                'New User Comment',
                "User {$user->name} left a comment: {$comment}"
            );
        }
    }

    /**
     * Handle data export request event
     */
    public function handleDataExportRequest(User $user)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'user_notification',
                'export_data',
                'Data Export Request',
                "User {$user->name} has requested to export their data"
            );
        }
    }

    /**
     * Handle data deletion request event
     */
    public function handleDataDeletionRequest(User $user)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AdminNotificationController::createNotification(
                $admin->id,
                'user_notification',
                'delete_data',
                'Data Deletion Request',
                "User {$user->name} has requested to delete their data (GDPR/HIPAA request)"
            );
        }
    }
}