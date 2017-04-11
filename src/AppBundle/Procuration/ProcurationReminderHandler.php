<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailjet\MailjetService;

class ProcurationReminderHandler
{
    private $mailjet;
    private $factory;

    public function __construct(MailjetService $mailjet, ProcurationProxyMessageFactory $factory)
    {
        $this->mailjet = $mailjet;
        $this->factory = $factory;
    }

    public function remind(ProcurationRequest $request)
    {
        $this->mailjet->sendMessage($this->factory->createProxyReminderMessage($request));
        $request->remind();
    }
}
