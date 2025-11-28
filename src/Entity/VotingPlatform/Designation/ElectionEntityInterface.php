<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\Designation;

interface ElectionEntityInterface
{
    public function getDesignation(): ?Designation;

    public function isCandidacyPeriodActive(): bool;

    public function isOngoing(): bool;

    public function getDesignationType(): string;

    public function getVoteStartDate(): ?\DateTime;

    public function getVoteEndDate(): ?\DateTime;
}
