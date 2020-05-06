<?php

namespace App\Entity;

trait AuthoredTrait
{
    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", fetch="EAGER")
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
