<?php

namespace App\TerritorialCouncil\Event;

use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\VotingPlatform\Event\TerritorialCouncilCandidacyEvent;

class CandidacyInvitationEvent extends TerritorialCouncilCandidacyEvent
{
    private $invitation;
    private $previouslyInvitedMembership;

    public function __construct(
        CandidacyInterface $candidacy,
        CandidacyInvitation $invitation,
        TerritorialCouncilMembership $previouslyInvitedMembership = null
    ) {
        parent::__construct($candidacy);

        $this->invitation = $invitation;
        $this->previouslyInvitedMembership = $previouslyInvitedMembership;
    }

    public function getInvitation(): CandidacyInvitation
    {
        return $this->invitation;
    }

    public function getPreviouslyInvitedMembership(): ?TerritorialCouncilMembership
    {
        return $this->previouslyInvitedMembership;
    }
}
