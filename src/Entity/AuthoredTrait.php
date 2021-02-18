<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait AuthoredTrait
{
    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", fetch="EAGER")
     *
     * @SymfonySerializer\Groups({"cause_read"})
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
