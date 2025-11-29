<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use App\Entity\Adherent;

interface CampaignStatisticsInterface
{
    public function getCampaign(): Campaign;

    public function getStatus(): string;

    public function setStatus(string $status): void;

    public function isTodo(): bool;

    public function isOngoing(): bool;

    public function isCompleted(): bool;

    public function getClosedAt(): ?\DateTimeInterface;

    public function setClosedAt(?\DateTimeInterface $closedAt): void;

    public function getClosedBy(): ?Adherent;

    public function setClosedBy(?Adherent $closedBy): void;
}
