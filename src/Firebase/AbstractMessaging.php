<?php

namespace App\Firebase;

use App\Firebase\Notification\MulticastNotificationInterface;
use App\Firebase\Notification\NotificationInterface;
use App\Firebase\Notification\TopicNotificationInterface;
use Kreait\Firebase\Messaging as BaseMessaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

abstract class AbstractMessaging
{
    private const MULTICAST_MAX_TOKENS = 500;

    private $messaging;

    public function __construct(BaseMessaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function send(NotificationInterface $notification): void
    {
        if ($notification instanceof TopicNotificationInterface) {
            $this->sendToTopic($notification);
        } elseif ($notification instanceof MulticastNotificationInterface) {
            $this->sendToDevices($notification);
        } else {
            throw new \InvalidArgumentException(sprintf('%s" is neither a topic nor a multicast notification.', \get_class($notification)));
        }
    }

    private function sendToTopic(TopicNotificationInterface $notification): void
    {
        $message = $this->createTopicMessage($notification)
            ->withNotification($this->createNotification($notification))
        ;

        $this->messaging->send($message);
    }

    private function sendToDevices(MulticastNotificationInterface $notification): void
    {
        $message = $this->createMessage()
            ->withNotification($this->createNotification($notification))
        ;

        foreach (array_chunk($notification->getTokens(), self::MULTICAST_MAX_TOKENS) as $chunk) {
            $this->messaging->sendMulticast($message, $chunk);
        }
    }

    private function createMessage(): CloudMessage
    {
        return CloudMessage::new();
    }

    private function createTopicMessage(TopicNotificationInterface $notification): CloudMessage
    {
        return CloudMessage::withTarget('topic', $notification->getTopic());
    }

    private function createNotification(NotificationInterface $notification): Notification
    {
        return Notification::create($notification->getTitle(), $notification->getBody());
    }
}
