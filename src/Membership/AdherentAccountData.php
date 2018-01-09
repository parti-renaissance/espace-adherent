<?php

namespace AppBundle\Membership;

use JMS\Serializer\Annotation as Serializer;

class AdherentAccountData
{
    /**
     * @Serializer\Type("string")
     */
    private $firstName;

    /**
     * @Serializer\Type("string")
     */
    private $lastName;

    /**
     * @Serializer\Type("string")
     */
    private $emailAddress;

    /**
     * @Serializer\Type("string")
     */
    private $zipCode;

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
