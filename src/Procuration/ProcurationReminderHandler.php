<?php

namespace AppBundle\Procuration;

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

    public function remind(array $requests)
    {
        $this->mailjet->sendMessage($this->factory->createProxyReminderMessage($requests));

        foreach ($requests as $request) {
            $request->remind();
        }
    }
}
