<?php

namespace App\Firebase;

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

    public function sendNotificationToTopic(string $topic, string $title, string $body): void
    {
        $message = $this
            ->createTopicMessage($topic)
            ->withNotification($this->createNotification($title, $body))
        ;

        $this->messaging->send($message);
    }

    public function sendNotificationToDevices(array $tokens, string $title, string $body): void
    {
        $message = $this
            ->createMessage()
            ->withNotification($this->createNotification($title, $body))
        ;

        foreach (array_chunk($tokens, self::MULTICAST_MAX_TOKENS) as $chunk) {
            $this->messaging->sendMulticast($message, $chunk);
        }
    }

    private function createMessage(): CloudMessage
    {
        return CloudMessage::new();
    }

    private function createTopicMessage(string $topic): CloudMessage
    {
        return CloudMessage::withTarget('topic', $topic);
    }

    private function createNotification(string $title, string $body): Notification
    {
        return Notification::create($title, $body);
    }
}
