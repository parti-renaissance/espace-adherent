<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

class NationalEventInscriptionChangeCommand implements SynchronizeMessageInterface
{
    public function __construct(
        public readonly UuidInterface $uuid,
        public readonly ?string $oldEmailAddress = null,
    ) {
    }
}
