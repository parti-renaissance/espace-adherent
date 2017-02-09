<?php

namespace AppBundle\Referent;

use AppBundle\Mailjet\MailjetService;

class ReferentMessageHandler
{
    private $mailjet;

    public function __construct(MailjetService $mailjet)
    {
        $this->mailjet = $mailjet;
    }

    public function handle(ReferentMessage $referentMessage)
    {
        // TODO Implement
    }
}
