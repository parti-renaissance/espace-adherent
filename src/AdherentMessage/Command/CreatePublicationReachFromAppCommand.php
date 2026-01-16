<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Messenger\Message\SequentialMessageInterface;

class CreatePublicationReachFromAppCommand implements SequentialMessageInterface
{
    public function __construct(
        public readonly string $publicationUuid,
        public readonly int $adherentId,
        public readonly \DateTimeInterface $createdAt,
    ) {
    }
}
