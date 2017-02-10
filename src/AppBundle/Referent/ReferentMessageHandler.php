<?php

namespace AppBundle\Referent;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\ReferentMessage;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;

class ReferentMessageHandler
{
    private $mailjet;

    public function __construct(MailjetService $mailjet)
    {
        $this->mailjet = $mailjet;
    }

    public function handle(ReferentMessageModel $referentMessage)
    {
        $this->mailjet->sendMessage(ReferentMessage::createFromModel($referentMessage));
    }
}
