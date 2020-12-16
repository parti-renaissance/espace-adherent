<?php

namespace App\Firebase;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class JeMarcheMessaging
{
    private $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function sendNotification(string $topic, string $title, string $body): void
    {
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification($this->createNotification($title, $body))
        ;

        $this->messaging->send($message);
    }

    private function createNotification(string $title, string $body): Notification
    {
        return Notification::create($title, $body);
    }
}
