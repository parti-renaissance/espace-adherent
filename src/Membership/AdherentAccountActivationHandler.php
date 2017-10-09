<?php

namespace AppBundle\Membership;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentAccountConfirmationMessage;

class AdherentAccountActivationHandler
{
    private $adherentManager;
    private $committeeManager;
    private $mailer;

    public function __construct(
        AdherentManager $adherentManager,
        CommitteeManager $committeeManager,
        MailerService $mailer
    ) {
        $this->adherentManager = $adherentManager;
        $this->committeeManager = $committeeManager;
        $this->mailer = $mailer;
    }

    public function handle(Adherent $adherent, AdherentActivationToken $token)
    {
        $this->adherentManager->activateAccount($adherent, $token);

        $this->mailer->sendMessage(AdherentAccountConfirmationMessage::createFromAdherent(
            $adherent,
            $this->adherentManager->countActiveAdherents(),
            $this->committeeManager->countApprovedCommittees()
        ));
    }
}
