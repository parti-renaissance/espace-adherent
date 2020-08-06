<?php

namespace App\Entity\VotingPlatform\Designation;

interface ElectionEntityInterface
{
    public function getDesignation(): Designation;

    public function isCandidacyPeriodActive(): bool;
}
