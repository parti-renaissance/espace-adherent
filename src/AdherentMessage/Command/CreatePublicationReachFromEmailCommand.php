<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class CreatePublicationReachFromEmailCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        UuidInterface $uuid,
        public readonly int $countRetry = 0,
    ) {
        parent::__construct($uuid);
    }
}
