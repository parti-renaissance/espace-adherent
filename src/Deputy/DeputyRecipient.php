<?php

namespace AppBundle\Deputy;

class DeputyRecipient
{
    private $emailAddress;
    private $firstName;
    private $lastName;

    public function __construct(string $emailAddress, string $firstName, string $lastName = null)
    {
        $this->emailAddress = $emailAddress;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
