<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface ElectedRepresentativeChangeCommandInterface extends SynchronizeMessageInterface
{
    public function getUuid(): UuidInterface;

    public function getOldEmailAddress(): ?string;
}
