<?php

namespace App\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Mailer\MailerService;
use App\Mailer\Message\Procuration\ProcurationProxyConfirmationMessage;
use App\Mailer\Message\Procuration\ProcurationRequestConfirmationMessage;

class ProcurationNotifier
{
    public function __construct(private readonly MailerService $transactionalMailer)
    {
    }

    public function sendRequestConfirmation(Request $request): void
    {
        $this->transactionalMailer->sendMessage(ProcurationRequestConfirmationMessage::create($request));
    }

    public function sendProxyConfirmation(Proxy $proxy): void
    {
        $this->transactionalMailer->sendMessage(ProcurationProxyConfirmationMessage::create($proxy));
    }
}
