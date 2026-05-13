<?php

declare(strict_types=1);

namespace App\Entity\Adherent\Note;

use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class AdherentNoteAuthor
{
    public const TYPE_ADD = 'add';
    public const TYPE_EDIT = 'edit';

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AdherentNote::class, inversedBy: 'authors')]
    public AdherentNote $note;

    #[Groups(['adherent_note:read'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $author;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $editedAt;

    #[ORM\Column(type: 'string', length: 10)]
    public string $type;

    #[ORM\Column(type: 'text')]
    public string $content;

    public function getId(): ?int
    {
        return $this->id;
    }
}
