<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class CreatePublicationReachFromEmailCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        Uuid $uuid,
        public readonly int $countRetry = 0,
    ) {
        parent::__construct($uuid);
    }
}
