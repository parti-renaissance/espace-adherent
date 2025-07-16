<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class NationalEventInscriptionChangeCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly ?string $oldEmailAddress = null,
    ) {
    }
}
