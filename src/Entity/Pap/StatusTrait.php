<?php

namespace App\Entity\Pap;

use App\Entity\Adherent;
use App\Pap\BuildingStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait StatusTrait
{
    /**
     * @ORM\Column(length=25)
     */
    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    private string $status;

    /**
     * @ORM\Column(length=25, nullable=true)
     */
    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    private ?string $statusDetail = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $closedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Adherent $closedBy = null;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatusDetail(): ?string
    {
        return $this->statusDetail;
    }

    public function setStatusDetail(?string $statusDetail): void
    {
        $this->statusDetail = $statusDetail;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): void
    {
        $this->closedAt = $closedAt;
    }

    public function getClosedBy(): ?Adherent
    {
        return $this->closedBy;
    }

    public function setClosedBy(?Adherent $closedBy): void
    {
        $this->closedBy = $closedBy;
    }

    public function isTodo(): bool
    {
        return BuildingStatusEnum::TODO === $this->status;
    }

    public function isOngoing(): bool
    {
        return BuildingStatusEnum::ONGOING === $this->status;
    }

    public function isCompleted(): bool
    {
        return BuildingStatusEnum::COMPLETED === $this->status;
    }
}
