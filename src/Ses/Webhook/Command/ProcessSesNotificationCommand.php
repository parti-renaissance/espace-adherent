<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class ProcessSesNotificationCommand implements AsynchronousMessageInterface
{
    public function __construct(public readonly array $payload)
    {
    }
}
