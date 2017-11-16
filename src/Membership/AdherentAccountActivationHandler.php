<?php

namespace AppBundle\Membership;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentAccountConfirmationMessage;
use AppBundle\Security\AuthenticationUtils;

class AdherentAccountActivationHandler
{
    private $adherentManager;
    private $committeeManager;
    private $mailer;
    private $authenticator;

    public function __construct(
        AdherentManager $adherentManager,
        CommitteeManager $committeeManager,
        MailerService $mailer,
        AuthenticationUtils $authenticator
    ) {
        $this->adherentManager = $adherentManager;
        $this->committeeManager = $committeeManager;
        $this->mailer = $mailer;
        $this->authenticator = $authenticator;
    }

    public function handle(Adherent $adherent, AdherentActivationToken $token)
    {
        $this->adherentManager->activateAccount($adherent, $token);
        $this->authenticator->authenticateAdherent($adherent);

        $this->mailer->sendMessage(AdherentAccountConfirmationMessage::createFromAdherent(
            $adherent,
            $this->adherentManager->countActiveAdherents(),
            $this->committeeManager->countApprovedCommittees()
        ));
    }
}
