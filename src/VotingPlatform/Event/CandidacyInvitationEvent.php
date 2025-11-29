<?php

declare(strict_types=1);

namespace App\VotingPlatform\Event;

use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;

class CandidacyInvitationEvent extends BaseCandidacyEvent
{
    private $invitedCandidacy;
    private $invitations;
    private $previouslyInvitedMemberships;

    /**
     * @param CandidacyInvitationInterface[] $invitations
     * @param CommitteeMembership[]          $previouslyInvitedMemberships
     */
    public function __construct(
        CandidacyInterface $candidacy,
        ?CandidacyInterface $invitedCandidacy,
        array $invitations = [],
        array $previouslyInvitedMemberships = [],
    ) {
        parent::__construct($candidacy);

        $this->invitedCandidacy = $invitedCandidacy;
        $this->invitations = $invitations;
        $this->previouslyInvitedMemberships = $previouslyInvitedMemberships;
    }

    public function getInvitedCandidacy(): ?CandidacyInterface
    {
        return $this->invitedCandidacy;
    }

    /**
     * @return CandidacyInvitationInterface[]
     */
    public function getInvitations(): array
    {
        return $this->invitations;
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getPreviouslyInvitedMemberships(): array
    {
        return $this->previouslyInvitedMemberships;
    }
}
