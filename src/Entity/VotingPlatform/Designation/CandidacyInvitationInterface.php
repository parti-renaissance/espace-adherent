<?php

namespace App\Entity\VotingPlatform\Designation;

interface CandidacyInvitationInterface
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    public function setCandidacy(CandidacyInterface $candidacy): void;

    public function isPending(): bool;
}
