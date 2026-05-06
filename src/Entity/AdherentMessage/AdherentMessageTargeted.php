<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\Entity\Adherent;
use App\Repository\AdherentMessageTargetedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdherentMessageTargetedRepository::class)]
#[ORM\Index(fields: ['message'])]
#[ORM\UniqueConstraint(fields: ['adherent', 'message'])]
class AdherentMessageTargeted
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public AdherentMessage $message;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?Adherent $adherent = null;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $targetedAt;

    public function __construct(AdherentMessage $message, ?Adherent $adherent, ?\DateTimeInterface $targetedAt = null)
    {
        $this->message = $message;
        $this->adherent = $adherent;
        $this->targetedAt = $targetedAt ?? new \DateTimeImmutable();
    }
}
