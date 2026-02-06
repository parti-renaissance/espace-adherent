<?php

declare(strict_types=1);

namespace App\Procuration;

use App\Entity\Adherent;
use App\Entity\Procuration\ProcurationRequest;
use App\Entity\Procuration\Proxy;
use App\Entity\Procuration\Request;
use App\Entity\Procuration\Round;
use App\Mailer\MailerService;
use App\Mailer\Message\Procuration\ProcurationInitialProxyReminderMessage;
use App\Mailer\Message\Procuration\ProcurationInitialRequestReminderMessage;
use App\Mailer\Message\Procuration\ProcurationMatchReminderMessage;
use App\Mailer\Message\Procuration\ProcurationProxyConfirmationMessage;
use App\Mailer\Message\Procuration\ProcurationRequestConfirmationMessage;
use App\Mailer\Message\Procuration\ProcurationRequestMatchedConfirmationMessage;
use App\Mailer\Message\Procuration\ProcurationRequestUnmatchedConfirmationMessage;

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

    public function sendMatchConfirmation(Request $request, Proxy $proxy, Round $round, ?Adherent $matcher = null): void
    {
        $this->transactionalMailer->sendMessage(ProcurationRequestMatchedConfirmationMessage::create($request, $proxy, $round, $matcher));
    }

    public function sendUnmatchConfirmation(Request $request, Proxy $proxy, Round $round, ?Adherent $matcher = null): void
    {
        $this->transactionalMailer->sendMessage(ProcurationRequestUnmatchedConfirmationMessage::create($request, $proxy, $round, $matcher));
    }

    public function sendInitialRequestReminder(ProcurationRequest $procurationRequest): void
    {
        $message = $procurationRequest->isForRequest()
            ? ProcurationInitialRequestReminderMessage::create($procurationRequest)
            : ProcurationInitialProxyReminderMessage::create($procurationRequest);

        $this->transactionalMailer->sendMessage($message);
    }

    public function sendMatchReminder(Request $request, Proxy $proxy, Round $round, ?Adherent $matcher = null): void
    {
        $this->transactionalMailer->sendMessage(ProcurationMatchReminderMessage::create($request, $proxy, $round, $matcher));
    }
}
