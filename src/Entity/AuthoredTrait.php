<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait AuthoredTrait
{
    /**
     * @var Adherent|null
     */
    #[Groups(['action_read', 'action_read_list', 'action_write', 'survey_replies_list', 'jecoute_news_read_dc', 'user_document:read'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    protected $author;

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
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
