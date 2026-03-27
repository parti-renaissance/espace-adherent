<?php

declare(strict_types=1);

namespace App\AppSession\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class UpdateAdherentLastLoginCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly UuidInterface $adherentUuid,
        public readonly \DateTimeImmutable $loginAt = new \DateTimeImmutable(),
    ) {
    }
}
