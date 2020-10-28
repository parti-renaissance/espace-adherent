<?php

namespace App\VotingPlatform\Event;

use App\Entity\CommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;

class CandidacyInvitationEvent extends BaseCandidacyEvent
{
    private $invitation;
    private $previouslyInvitedMembership;

    /**
     * @param TerritorialCouncilMembership|CommitteeMembership $previouslyInvitedMembership
     */
    public function __construct(
        CandidacyInterface $candidacy,
        CandidacyInvitationInterface $invitation = null,
        $previouslyInvitedMembership = null
    ) {
        parent::__construct($candidacy);

        $this->invitation = $invitation;
        $this->previouslyInvitedMembership = $previouslyInvitedMembership;
    }

    public function getInvitation(): ?CandidacyInvitationInterface
    {
        return $this->invitation;
    }

    public function getPreviouslyInvitedMembership(): ?object
    {
        return $this->previouslyInvitedMembership;
    }
}
