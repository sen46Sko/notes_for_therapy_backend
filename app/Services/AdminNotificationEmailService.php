<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminNotificationMail;

class AdminNotificationEmailService
{
    /**
     *
     */
    public function sendInstantNotification(Admin $admin, AdminNotification $notification)
    {
        $settings = $admin->notificationSettings;

        if (!$settings || !$settings->notifications_enabled || $settings->notification_frequency !== 'instant') {
            return;
        }

        Mail::to($admin->email)->send(new AdminNotificationMail($notification));
    }

    /**
     *
     */
    public function sendDailyDigest(Admin $admin)
    {
        $this->sendDigest($admin, 'daily', now()->subDay());
    }

    /**
     *
     */
    public function sendWeeklyDigest(Admin $admin)
    {
        $this->sendDigest($admin, 'weekly', now()->subWeek());
    }

    /**
     *
     */
    public function sendMonthlyDigest(Admin $admin)
    {
        $this->sendDigest($admin, 'monthly', now()->subMonth());
    }

    /**
     *
     */
    private function sendDigest(Admin $admin, $frequency, $since)
    {

        $settings = $admin->notificationSettings;

        if (!$settings || !$settings->notifications_enabled || $settings->notification_frequency !== $frequency) {
            return;
        }

        $notifications = AdminNotification::where('admin_id', $admin->id)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notifications->isEmpty()) {
            return;
        }

        Mail::to($admin->email)->send(new AdminNotificationDigestMail($admin, $notifications, $frequency));
    }
}