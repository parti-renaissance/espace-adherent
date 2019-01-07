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
     * @JMS\Groups({"user_profile", "public"})
     * @JMS\SerializedName("firstName")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "thread_comment_read", "vote_read", "idea_read", "thread_list_read"})
     */
    private $firstName = '';

    /**
     * @ORM\Column(length=50)
     *
     * @JMS\Groups({"user_profile", "public"})
     * @JMS\SerializedName("lastName")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "thread_comment_read", "vote_read", "idea_read", "thread_list_read"})
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
