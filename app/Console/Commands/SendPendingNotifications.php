<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Notifications\DefaultNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helper\NotificationRepeatHelper;
use App\Models\Event;
use App\Models\Homework;
use App\Models\UserNotificationSetting;

class SendPendingNotifications extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Send pending notifications';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();

        $notifications = Notification::where('status', 'Pending')
                                     ->where('show_at', '<=', $now)
                                     ->get();

        foreach ($notifications as $notification) {
            try {
                $user = $notification->user;
                $userNotificationSettings = UserNotificationSetting::where('user_id', $user->id)->first();

                // Check if notifications are enabled for this user
                if (!$userNotificationSettings->show_notifications) {
                    $this->handleDisabledNotification($notification);
                    continue;
                }

                // Check if the specific notification type is enabled
                if (!$this->isNotificationTypeAllowed($notification->type, $userNotificationSettings)) {
                    Log::info("Notification type {$notification->type} is not allowed for user {$user->id}. Skipping.");
                    continue;
                }

                // Prepare notification data
                $notificationData = [
                    'type' => $notification->type,
                    'status' => $notification->status,
                    'show_at' => $notification->show_at,
                    'repeat' => $notification->repeat ?? 'null',
                ];

                // Set sound based on user preference
                $notificationData['sound'] = $userNotificationSettings->sound ? 'default' : 'none';

                // Set preview based on user preference
                if (!$userNotificationSettings->preview) {
                    $notificationData['alert'] = 'New notification';
                }

                // Send the notification
                $user->notify(new DefaultNotification(
                    $notification->title,
                    $notification->description,
                    null,
                    $notificationData
                ));

                // Update notification status
                $notification->update(['status' => 'Sent']);

                // Handle notification repetition
                $this->handleNotificationRepetition($notification);

                Log::info("Notification of type {$notification->type} sent successfully to user {$user->id}.");
            } catch (\Exception $e) {
                Log::error("Failed to send notification of type {$notification->type}: " . $e->getMessage());
            }
        }

        return 0;
    }

    private function handleDisabledNotification($notification)
    {
        Log::info("Notifications are disabled for user {$notification->user_id}. Creating next notification and removing current one.");

        try {
            // Create the next notification
            $nextNotification = $this->createNextNotification($notification);

            if ($nextNotification) {
                Log::info("Next notification created successfully. ID: {$nextNotification->id}, Show at: {$nextNotification->show_at}");
            } else {
                Log::info("No next notification created. This might be the last in the sequence.");
            }

            // Remove the current notification
            $notification->delete();
            Log::info("Current notification (ID: {$notification->id}) has been removed.");
        } catch (\Exception $e) {
            Log::error("Error handling disabled notification: " . $e->getMessage());
        }
    }

    private function createNextNotification($notification)
    {
        $notificationHelper = new NotificationRepeatHelper($notification->repeat);
        $nextShowAt = $notificationHelper->getNextNotificationDate($notification->show_at);

        if (!$nextShowAt) {
            return null; // No next notification if there's no next date
        }

        $newNotification = $notification->replicate();
        $newNotification->show_at = $nextShowAt;
        $newNotification->status = 'Pending';

        // Check termination conditions based on notification type
        switch ($notification->type) {
            case 'Event':
            case 'Event_Alert':
                $event = Event::find($notification->entity_id);
                if (!$event || Carbon::parse($event->start_at) <= Carbon::now()) {
                    return null;
                }
                break;
            case 'Homework':
                $homework = Homework::find($notification->entity_id);
                if (!$homework || Carbon::parse($homework->due_date) <= Carbon::now() || $homework->completed_at !== null) {
                    return null;
                }
                $newNotification->save();
                $homework->notification_id = $newNotification->id;
                $homework->save();
                break;
            case 'Goal':
                // Add any specific logic for Goal repetition if needed
                break;
        }

        $newNotification->save();
        return $newNotification;
    }

    private function isNotificationTypeAllowed($type, $settings)
    {
        switch ($type) {
            case 'Goal':
                return $settings->goals;
            case 'Homework':
                return $settings->homework;
            case 'Event_Alert':
            case 'Event':
                return $settings->reminders; // Assuming Event and Event_Alert fall under 'reminders'
            default:
                Log::warning("Unknown notification type: {$type}");
                return false;
        }
    }

    private function handleNotificationRepetition($notification)
    {
        $nextNotification = $this->createNextNotification($notification);

        if ($nextNotification) {
            Log::info("Notification repeated successfully. Type: {$notification->type}, Old show_at: {$notification->show_at}, New show_at: {$nextNotification->show_at}, Notification ID: {$notification->id}, New Notification ID: {$nextNotification->id}");
        } else {
            Log::info("No next notification created for notification ID: {$notification->id}. This might be the last in the sequence.");
        }
    }
}
