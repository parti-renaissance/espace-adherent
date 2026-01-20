<?php

declare(strict_types=1);

namespace App\Donation\Command;

use App\Messenger\Message\SequentialMessageInterface;

class ReceivePayboxIpnResponseCommand implements SequentialMessageInterface
{
    public function __construct(public readonly array $payload)
    {
    }
}
