<?php

declare(strict_types=1);

namespace App\Entity\NationalEvent;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table('national_event_inscription_scan')]
class TicketScan
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(inversedBy: 'scans')]
    public EventInscription $inscription;

    #[ORM\Column(nullable: true)]
    public ?string $inscriptionStatus = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $scannedBy = null;

    public function __construct(Adherent $scannedBy, string $inscriptionStatus)
    {
        $this->uuid = Uuid::uuid4();
        $this->scannedBy = $scannedBy;
        $this->inscriptionStatus = $inscriptionStatus;
        $this->createdAt = new \DateTimeImmutable();
    }
}
