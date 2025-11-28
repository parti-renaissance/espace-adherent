<?php

declare(strict_types=1);

namespace App\Procuration\V2;

use App\Entity\Adherent;
use App\Entity\ProcurationV2\ProcurationRequest;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\Round;
use App\Mailer\MailerService;
use App\Mailer\Message\Procuration\V2\ProcurationInitialProxyReminderMessage;
use App\Mailer\Message\Procuration\V2\ProcurationInitialRequestReminderMessage;
use App\Mailer\Message\Procuration\V2\ProcurationMatchReminderMessage;
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
