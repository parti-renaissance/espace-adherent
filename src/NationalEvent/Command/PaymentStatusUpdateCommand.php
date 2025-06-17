<?php

namespace App\NationalEvent\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class PaymentStatusUpdateCommand implements AsynchronousMessageInterface
{
    public function __construct(public readonly array $payload)
    {
    }
}
