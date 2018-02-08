<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Security\AuthenticationUtils;

class AdherentAccountActivationHandler
{
    private $adherentManager;
    private $authenticator;

    public function __construct(AdherentManager $adherentManager, AuthenticationUtils $authenticator)
    {
        $this->adherentManager = $adherentManager;
        $this->authenticator = $authenticator;
    }

    public function handle(Adherent $adherent, AdherentActivationToken $token)
    {
        $this->adherentManager->activateAccount($adherent, $token);
        $this->authenticator->authenticateAdherent($adherent);
    }
}
