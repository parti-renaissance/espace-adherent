<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Command;

use App\Ses\Webhook\SesEngagementMessageInterface;

class RecordSesEngagementCommand implements SesEngagementMessageInterface
{
    public function __construct(public readonly array $payload)
    {
    }
}
