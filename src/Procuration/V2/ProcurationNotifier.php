<?php

namespace App\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Mailer\MailerService;
use App\Mailer\Message\Procuration\V2\ProcurationProxyConfirmationMessage;
use App\Mailer\Message\Procuration\V2\ProcurationRequestConfirmationMessage;
use App\Mailer\Message\Procuration\V2\ProcurationRequestMatchedConfirmationMessage;
use App\Mailer\Message\Procuration\V2\ProcurationRequestUnmatchedConfirmationMessage;

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

    public function sendMatchConfirmation(Request $request, Proxy $proxy): void
    {
        $this->transactionalMailer->sendMessage(ProcurationRequestMatchedConfirmationMessage::create($request, $proxy));
    }

    public function sendUnmatchConfirmation(Request $request, Proxy $proxy): void
    {
        $this->transactionalMailer->sendMessage(ProcurationRequestUnmatchedConfirmationMessage::create($request, $proxy));
    }
}
