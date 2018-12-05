<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait EntityPersonNameTrait
{
    /**
     * @ORM\Column(length=50)
     *
     * @SymfonySerializer\Groups("idea_list_read")
     * @JMS\Groups({"user_profile", "public"})
     * @JMS\SerializedName("firstName")
     */
    private $firstName = '';

    /**
     * @ORM\Column(length=50)
     *
     * @SymfonySerializer\Groups("idea_list_read")
     * @JMS\Groups({"user_profile", "public"})
     * @JMS\SerializedName("lastName")
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
