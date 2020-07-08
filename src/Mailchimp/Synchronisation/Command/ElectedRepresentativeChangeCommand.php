<?php

namespace App\Mailchimp\Synchronisation\Command;

use Ramsey\Uuid\UuidInterface;

class ElectedRepresentativeChangeCommand implements ElectedRepresentativeChangeCommandInterface
{
    private $uuid;
    private $oldEmailAddress;

    public function __construct(UuidInterface $uuid, string $oldEmailAddress = null)
    {
        $this->uuid = $uuid;

        if ($oldEmailAddress) {
            $this->oldEmailAddress = strtolower($oldEmailAddress);
        }
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getOldEmailAddress(): ?string
    {
        return $this->oldEmailAddress;
    }
}
