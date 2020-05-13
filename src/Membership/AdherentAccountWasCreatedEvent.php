<?php

namespace App\Membership;

use App\Entity\Adherent;

final class AdherentAccountWasCreatedEvent extends AdherentEvent
{
    private $membershipRequest;

    public function __construct(Adherent $adherent, MembershipRequest $membershipRequest = null)
    {
        parent::__construct($adherent);

        $this->membershipRequest = $membershipRequest;
    }

    public function getMembershipRequest(): ?MembershipRequest
    {
        return $this->membershipRequest;
    }
}
