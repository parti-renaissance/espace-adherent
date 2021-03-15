<?php

namespace App\VotingPlatform\Event;

use App\Entity\CommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;

class CandidacyInvitationEvent extends BaseCandidacyEvent
{
    private $invitations;
    private $previouslyInvitedMemberships;

    /**
     * @param CandidacyInvitationInterface[]                       $invitations
     * @param TerritorialCouncilMembership[]|CommitteeMembership[] $previouslyInvitedMemberships
     */
    public function __construct(
        CandidacyInterface $candidacy,
        array $invitations = [],
        array $previouslyInvitedMemberships = []
    ) {
        parent::__construct($candidacy);

        $this->invitations = $invitations;
        $this->previouslyInvitedMemberships = $previouslyInvitedMemberships;
    }

    /**
     * @return CandidacyInvitationInterface[]
     */
    public function getInvitations(): array
    {
        return $this->invitations;
    }

    /**
     * @return TerritorialCouncilMembership[]|CommitteeMembership[]
     */
    public function getPreviouslyInvitedMemberships(): array
    {
        return $this->previouslyInvitedMemberships;
    }
}
