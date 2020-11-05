<?php

namespace App\Procuration;

use App\Mailer\MailerService;

class ProcurationReminderHandler
{
    private $mailer;
    private $factory;

    public function __construct(MailerService $transactionalMailer, ProcurationProxyMessageFactory $factory)
    {
        $this->mailer = $transactionalMailer;
        $this->factory = $factory;
    }

    public function remind(array $requests)
    {
        $this->mailer->sendMessage($this->factory->createProxyReminderMessage($requests));

        foreach ($requests as $request) {
            $request->remind();
        }
    }
}
