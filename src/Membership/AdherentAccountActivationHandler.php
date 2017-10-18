<?php

namespace AppBundle\Membership;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\AdherentAccountConfirmationMessage;

class AdherentAccountActivationHandler
{
    private $adherentManager;
    private $committeeManager;
    private $mailjet;

    public function __construct(
        AdherentManager $adherentManager,
        CommitteeManager $committeeManager,
        MailjetService $mailjet
    ) {
        $this->adherentManager = $adherentManager;
        $this->committeeManager = $committeeManager;
        $this->mailjet = $mailjet;
    }

    public function handle(Adherent $adherent, AdherentActivationToken $token)
    {
        $this->adherentManager->activateAccount($adherent, $token);

        $this->mailjet->sendMessage(AdherentAccountConfirmationMessage::createFromAdherent(
            $adherent,
            $this->adherentManager->countActiveAdherents(),
            $this->committeeManager->countApprovedCommittees()
        ));
    }
}
