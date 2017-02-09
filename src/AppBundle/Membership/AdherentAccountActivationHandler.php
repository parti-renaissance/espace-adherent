<?php

namespace AppBundle\Membership;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\AdherentAccountConfirmationMessage;
use AppBundle\Security\AuthenticationUtils;

class AdherentAccountActivationHandler
{
    private $adherentManager;
    private $committeeManager;
    private $mailjet;
    private $authenticator;

    public function __construct(
        AdherentManager $adherentManager,
        CommitteeManager $committeeManager,
        MailjetService $mailjet,
        AuthenticationUtils $authenticator
    ) {
        $this->adherentManager = $adherentManager;
        $this->committeeManager = $committeeManager;
        $this->mailjet = $mailjet;
        $this->authenticator = $authenticator;
    }

    public function handle(Adherent $adherent, AdherentActivationToken $token)
    {
        $this->adherentManager->activateAccount($adherent, $token);
        $this->authenticator->authenticateAdherent($adherent);

        $this->mailjet->sendMessage(AdherentAccountConfirmationMessage::createFromAdherent(
            $adherent,
            $this->adherentManager->countActiveAdherents(),
            $this->committeeManager->countApprovedCommittees()
        ));
    }
}
