<?php

declare(strict_types=1);

namespace App\NationalEvent\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class PaymentStatusUpdateCommand implements AsynchronousMessageInterface
{
    public function __construct(public readonly array $payload)
    {
    }
}
