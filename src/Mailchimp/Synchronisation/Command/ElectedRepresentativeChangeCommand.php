<?php

namespace App\Mailchimp\Synchronisation\Command;

use Ramsey\Uuid\UuidInterface;

class ElectedRepresentativeChangeCommand implements ElectedRepresentativeChangeCommandInterface
{
    private $uuid;
    private $emailAddress;

    public function __construct(UuidInterface $uuid, string $emailAddress)
    {
        $this->uuid = $uuid;
        $this->emailAddress = strtolower($emailAddress);
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}
