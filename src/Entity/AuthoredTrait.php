<?php

namespace AppBundle\Entity;

trait AuthoredTrait
{
    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="Adherent", fetch="EAGER")
     */
    private $author;

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getAuthorFullName(): string
    {
        return $this->getAuthor()
            ? $this->getAuthor()->getFirstName().' '.$this->getAuthor()->getLastName()
            : 'Anonyme'
            ;
    }

    public function makeAnonymous(): void
    {
        $this->author = null;
    }
}
