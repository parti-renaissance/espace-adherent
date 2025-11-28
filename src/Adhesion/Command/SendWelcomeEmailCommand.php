<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class SendWelcomeEmailCommand extends UuidDefaultAsyncMessage
{
    public function __construct(UuidInterface $uuid, public readonly bool $renew = false)
    {
        parent::__construct($uuid);
    }
}
