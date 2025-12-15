<?php

declare(strict_types=1);

namespace App\Adherent\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class AdherentMergeCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly int $adherentSourceId,
        public readonly int $adherentTargetId,
    ) {
    }
}
