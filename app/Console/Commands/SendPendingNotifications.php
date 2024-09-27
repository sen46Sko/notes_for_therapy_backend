<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Notifications\DefaultNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helper\NotificationRepeatHelper;

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
                $notification->notify(new DefaultNotification($notification->title, $notification->description, null, [
                    'type' => $notification->type,
                    'status' => $notification->status,
                    'show_at' => $notification->show_at,
                    'repeat' => $notification->repeat ?? 'null',
                ]));
                // After sending the notification, update its status
                $notification->update(['status' => 'Sent']);

                try {

                    $notificationHelper = new NotificationRepeatHelper($notification->repeat);
                    $nextShowAt = $notificationHelper->getNextNotificationDate($notification->show_at);

                    $newNotification = $notification->replicate();
                    $newNotification->show_at = $nextShowAt;
                    $newNotification->status = 'Pending';

                    // Repeat termination conditions

                    if ($notification->type == 'Event') {
                        $event = Event::find($notification->entity_id);
                        if (Carbon::parse($event->start_at) > Carbon::now()) {
                            continue;
                        }
                        $newNotification->save();
                    }
                    else if ($notification->type == 'Homework') {
                        $homework = Homework::find($notification->entity_id);
                        if (Carbon::parse($homework->due_date) > Carbon::now()) {
                            continue;
                        }
                        if ($homework->completed_at != null) {
                            continue;
                        }

                        $newNotification->save();
                        $homework->notification_id = $newNotification->id;
                    }

                    $newNotification->save();

                    // Scheduling repeated notification



                    Log::info('Notification repeated successfully.
                        Old show_at: ' . $notification->show_at . '
                        New show_at: ' . $newNotification->show_at . '
                        Notification ID: ' . $notification->id . '
                        New Notification ID: ' . $newNotification->id . '
                    ');

                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    Log::error('Failed to parse repeat data');
                }

                Log::info('Notification sent successfully.');
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                Log::error('Failed to send notification.');
            }
        }

        return 0;
    }
}
