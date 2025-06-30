<?php

namespace App\Mailchimp\Synchronisation\Command;

use Ramsey\Uuid\UuidInterface;

class EventInscriptionChangeCommand
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly ?string $oldEmailAddress = null,
    ) {
    }
}
