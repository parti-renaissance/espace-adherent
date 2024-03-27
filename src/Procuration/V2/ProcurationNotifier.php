<?php

namespace App\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Mailer\MailerService;
use App\Mailer\Message\Procuration\V2\ProxyConfirmationMessage;
use App\Mailer\Message\Procuration\V2\RequestConfirmationMessage;
use App\Mailer\Message\Procuration\V2\RequestMatchedConfirmationMessage;
use App\Mailer\Message\Procuration\V2\RequestUnmatchedConfirmationMessage;

class ProcurationNotifier
{
    public function __construct(private readonly MailerService $transactionalMailer)
    {
    }

    public function sendRequestConfirmation(Request $request): void
    {
        $this->transactionalMailer->sendMessage(RequestConfirmationMessage::create($request));
    }

    public function sendProxyConfirmation(Proxy $proxy): void
    {
        $this->transactionalMailer->sendMessage(ProxyConfirmationMessage::create($proxy));
    }

    public function sendMatchConfirmation(Request $request, Proxy $proxy): void
    {
        $this->transactionalMailer->sendMessage(RequestMatchedConfirmationMessage::create($request, $proxy));
    }

    public function sendUnmatchConfirmation(Request $request, Proxy $proxy): void
    {
        $this->transactionalMailer->sendMessage(RequestUnmatchedConfirmationMessage::create($request, $proxy));
    }
}
