<?php

namespace App\Membership\Event;

use App\Entity\Adherent;
use App\Membership\MembershipRequest\MembershipInterface;

final class AdherentAccountWasCreatedEvent extends AdherentEvent
{
    private ?MembershipInterface $membershipRequest;

    public function __construct(Adherent $adherent, MembershipInterface $membershipRequest = null)
    {
        parent::__construct($adherent);

        $this->membershipRequest = $membershipRequest;
    }

    public function getMembershipRequest(): ?MembershipInterface
    {
        return $this->membershipRequest;
    }
}
