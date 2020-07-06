<?php

namespace App\Mailchimp\Synchronisation\Command;

use Ramsey\Uuid\UuidInterface;

class ElectedRepresentativeChangeCommand implements ElectedRepresentativeChangeCommandInterface
{
    private $uuid;
    private $emailAddress;
    private $removedTags;

    public function __construct(UuidInterface $uuid, string $emailAddress, array $removedTags = [])
    {
        $this->uuid = $uuid;
        $this->emailAddress = strtolower($emailAddress);
        $this->removedTags = $removedTags;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getRemovedTags(): array
    {
        return $this->removedTags;
    }
}
