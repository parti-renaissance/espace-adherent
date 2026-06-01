<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdherentSignupSourceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdherentSignupSourceRepository::class)]
#[ORM\Table(name: 'adherent_signup_source')]
#[ORM\UniqueConstraint(columns: ['adherent_id', 'source'])]
class AdherentSignupSource
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public Adherent $adherent;

    #[ORM\Column(length: 100)]
    public string $source;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $capturedAt;

    public function __construct(Adherent $adherent, string $source, ?\DateTimeImmutable $capturedAt = null)
    {
        $this->adherent = $adherent;
        $this->source = $source;
        $this->capturedAt = $capturedAt ?? new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
