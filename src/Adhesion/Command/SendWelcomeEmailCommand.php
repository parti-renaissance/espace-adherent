<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class SendWelcomeEmailCommand extends UuidDefaultAsyncMessage
{
    public function __construct(Uuid $uuid, public readonly bool $renew = false)
    {
        parent::__construct($uuid);
    }
}
