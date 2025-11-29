<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Mailer\Message\Message;

class SenderMessageMapper
{
    private array $mapping = [];

    public function __construct(array $mapping)
    {
        foreach ($mapping as $class => $row) {
            $this->mapping[$class] = new Sender($row['name'] ?? null, $row['email'] ?? null);
        }
    }

    public function findForMessage(Message $message): ?Sender
    {
        foreach ($this->mapping as $class => $sender) {
            if (is_a($message, $class)) {
                return $sender;
            }
        }

        return null;
    }
}
