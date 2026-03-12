<?php

declare(strict_types=1);

namespace App\Entity\AdherentCharter;

use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Table(name: 'adherent_charter')]
#[ORM\UniqueConstraint(columns: ['adherent_id', 'dtype'])]
abstract class AbstractAdherentCharter implements AdherentCharterInterface
{
    #[ORM\Column(type: 'smallint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $acceptedAt;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'charters')]
    private ?Adherent $adherent = null;

    public function __construct()
    {
        $this->acceptedAt = new \DateTimeImmutable();
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
