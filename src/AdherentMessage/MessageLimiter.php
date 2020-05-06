<?php

namespace App\AdherentMessage;

class MessageLimiter
{
    private $messagesLimits = [];

    public function __construct(array $messagesLimits)
    {
        foreach ($messagesLimits as $messageType => $limit) {
            if (!AdherentMessageTypeEnum::isValid($messageType)) {
                unset($messagesLimits[$messageType]);
            }
        }

        $this->messagesLimits = $messagesLimits;
    }

    public function support(string $messageType): bool
    {
        return isset($this->messagesLimits[$messageType]);
    }

    public function getLimit(string $messageType): ?int
    {
        return $this->messagesLimits[$messageType] ?? null;
    }
}
