<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Messenger\Message\SequentialMessageInterface;

class CreatePublicationReachFromPushCommand implements SequentialMessageInterface
{
    public function __construct(
        public readonly int $adherentMessageId,
        public readonly string $pushToken,
    ) {
    }
}
