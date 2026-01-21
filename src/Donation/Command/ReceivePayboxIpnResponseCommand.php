<?php

declare(strict_types=1);

namespace App\Donation\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class ReceivePayboxIpnResponseCommand implements AsynchronousMessageInterface
{
    public function __construct(public readonly array $payload)
    {
    }
}
