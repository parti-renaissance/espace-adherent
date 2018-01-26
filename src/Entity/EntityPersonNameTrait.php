<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityPersonNameTrait
{
    /**
     * @ORM\Column(length=50)
     */
    private $firstName = '';

    /**
     * @ORM\Column(length=50)
     */
    private $lastName = '';

    public function __toString(): string
    {
        return trim($this->getFullName());
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getPartialName(): string
    {
        return $this->firstName.' '.$this->getLastNameInitial();
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getLastNameInitial(): string
    {
        $normalized = preg_replace('/[^a-z]+/', '', strtolower($this->lastName));

        return strtoupper($normalized[0]).'.';
    }
}
