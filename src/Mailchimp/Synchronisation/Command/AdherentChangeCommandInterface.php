<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentChangeCommandInterface extends SynchronizeMessageInterface
{
    public function getUuid(): UuidInterface;

    public function getEmailAddress(): string;

    public function getRemovedTags(): array;
}
