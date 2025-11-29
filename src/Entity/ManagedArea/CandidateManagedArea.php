<?php

declare(strict_types=1);

namespace App\Entity\ManagedArea;

use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CandidateManagedArea
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Zone::class, cascade: ['persist'])]
    private $zone;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function isRegionalZone(): bool
    {
        return Zone::REGION === $this->getZone()->getType();
    }

    public function isDepartmentalZone(): bool
    {
        return Zone::DEPARTMENT === $this->getZone()->getType();
    }

    public function isCantonalZone(): bool
    {
        return Zone::CANTON === $this->getZone()->getType();
    }
}
