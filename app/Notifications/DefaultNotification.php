<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class DefaultNotification extends Notification
{
    private $title;
    private $body;
    private $image;
    private $metadata;

    public function __construct($title, $body, $image = null, $metadata = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->image = $image;
        $this->metadata = $metadata;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $sound = $this->metadata['sound'] ?? 'default';
        $alert = $this->metadata['alert'] ?? [
            'title' => $this->title,
            'body' => $this->body,
        ];

        return FcmMessage::create()
            ->setData($this->metadata ?? [])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($alert['title'])
                ->setBody($alert['body'])
                ->setImage($this->image))
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(AndroidNotification::create()
                        ->setColor('#0A0A0A')
                        ->setSound($sound)
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
                    ->setPayload([
                        'aps' => [
                            'alert' => $alert,
                            'sound' => $sound,
                        ]
                    ])
            );
    }

    public function fcmProject($notifiable, $message)
    {
        return 'app';
    }
}
