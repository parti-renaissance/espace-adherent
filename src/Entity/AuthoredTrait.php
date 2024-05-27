<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait AuthoredTrait
{
    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Groups({
     *     "action_read",
     *     "action_read_list",
     *     "action_write",
     * })
     */
    private $author;

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getAuthorFullName(): string
    {
        return $this->getAuthor()
            ? $this->getAuthor()->getFirstName().' '.$this->getAuthor()->getLastName()
            : 'Anonyme';
    }

    public function makeAnonymous(): void
    {
        $this->author = null;
    }
}
