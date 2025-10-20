<?php

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
