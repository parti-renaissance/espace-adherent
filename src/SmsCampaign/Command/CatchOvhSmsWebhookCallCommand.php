<?php

namespace App\SmsCampaign\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class CatchOvhSmsWebhookCallCommand implements AsynchronousMessageInterface
{
    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
