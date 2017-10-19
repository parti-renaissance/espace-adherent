<?php

namespace AppBundle\Membership;

use Ramsey\Uuid\UuidInterface;

class AdherentAccountData
{
    private $uuid;
    private $firstName;
    private $lastName;
    private $emailAddress;
    private $zipCode;

    public function __construct(
        UuidInterface $uuid,
        string $emailAddress,
        string $firstName,
        string $lastName,
        string $zipCode = null
    ) {
        $this->uuid = $uuid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->zipCode = $zipCode;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }
}
