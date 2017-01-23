<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;

final class AdherentAccountWasActivatedEvent extends AdherentEvent
{
    private $activationToken;

    public function __construct(Adherent $adherent, AdherentActivationToken $token)
    {
        parent::__construct($adherent);

        $this->activationToken = $token;
    }

    public function getActivationToken(): AdherentActivationToken
    {
        return $this->activationToken;
    }
}
