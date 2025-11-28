<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityAdherentBlameableTrait
{
    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $createdByAdherent;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $updatedByAdherent;

    public function getCreatedByAdherent(): ?Adherent
    {
        return $this->createdByAdherent;
    }

    public function setCreatedByAdherent(?Adherent $createdByAdherent): void
    {
        $this->createdByAdherent = $createdByAdherent;
    }

    public function getUpdatedByAdherent(): ?Adherent
    {
        return $this->updatedByAdherent;
    }

    public function setUpdatedByAdherent(?Adherent $updatedByAdherent): void
    {
        $this->updatedByAdherent = $updatedByAdherent;
    }
}
