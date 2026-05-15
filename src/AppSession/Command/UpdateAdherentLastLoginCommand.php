<?php

declare(strict_types=1);

namespace App\AppSession\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Symfony\Component\Uid\Uuid;

class UpdateAdherentLastLoginCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly Uuid $adherentUuid,
        public readonly \DateTimeInterface $loginAt = new \DateTime(),
    ) {
    }
}
