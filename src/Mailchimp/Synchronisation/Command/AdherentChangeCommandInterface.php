<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentChangeCommandInterface extends SynchronizeMessageInterface
{
    public function getUuid(): UuidInterface;

    public function getEmailAddress(): string;

    public function getRemovedTags(): array;
}
