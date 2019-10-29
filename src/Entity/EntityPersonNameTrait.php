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
     * @SymfonySerializer\Groups({"export", "idea_list_read", "idea_read", "idea_thread_list_read", "idea_thread_comment_read", "idea_vote_read"})
     *
     * @JMS\Groups({"adherent_change_diff", "user_profile", "public"})
     * @JMS\SerializedName("firstName")
     */
    private $firstName = '';

    /**
     * @ORM\Column(length=50)
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_read", "idea_thread_list_read", "idea_thread_comment_read", "idea_vote_read"})
     *
     * @JMS\Groups({"adherent_change_diff", "user_profile", "public"})
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
        return (string) $this->firstName;
    }

    public function getLastName(): string
    {
        return (string) $this->lastName;
    }

    /**
     * @SymfonySerializer\Groups({"export"})
     */
    public function getLastNameInitial(): string
    {
        $normalized = preg_replace('/[^a-z]+/', '', strtolower($this->lastName));

        return strtoupper($normalized[0]).'.';
    }
}
