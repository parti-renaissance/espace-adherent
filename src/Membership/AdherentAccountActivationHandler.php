<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Security\AuthenticationUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);

        $this->authenticator->authenticateAdherent($adherent);
    }
}
