<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Security\AuthenticationUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentAccountActivationHandler
{
    private $adherentManager;
    private $authenticator;
    private $dispatcher;

    public function __construct(
        AdherentManager $adherentManager,
        AuthenticationUtils $authenticator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->adherentManager = $adherentManager;
        $this->authenticator = $authenticator;
        $this->dispatcher = $dispatcher;
    }

    public function handle(Adherent $adherent, AdherentActivationToken $token): void
    {
        $this->adherentManager->activateAccount($adherent, $token);

        $this->dispatcher->dispatch(UserEvents::USER_VALIDATED, new UserEvent($adherent));

        $this->authenticator->authenticateAdherent($adherent);
    }
}
