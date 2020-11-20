<?php

namespace App\Entity\VotingPlatform\Designation;

interface ElectionEntityInterface
{
    public function getDesignation(): Designation;

    public function isCandidacyPeriodActive(): bool;

    public function isOngoing(): bool;

    public function getDesignationType(): string;

    public function getVoteEndDate(): ?\DateTime;
}
