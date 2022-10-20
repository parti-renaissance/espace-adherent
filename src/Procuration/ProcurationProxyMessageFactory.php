<?php

namespace App\Procuration;

use App\Entity\Adherent;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Mailer\Message\Procuration\ProcurationProxyCancelledMessage;
use App\Mailer\Message\Procuration\ProcurationProxyMatchedMessage;
use App\Mailer\Message\Procuration\ProcurationProxyRegistrationConfirmationMessage;
use App\Mailer\Message\Procuration\ProcurationProxyReminderMessage;
use App\Mailer\Message\Procuration\ProcurationRequestRegistrationConfirmationMessage;
use App\Mailer\Message\Procuration\ProcurationRequestReminderMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcurationProxyMessageFactory
{
    private $urlGenerator;
    private $replyToEmailAddress;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $replyToEmailAddress)
    {
        $this->urlGenerator = $urlGenerator;
        $this->replyToEmailAddress = $replyToEmailAddress;
    }

    public function createProxyCancelledMessage(
        ProcurationRequest $request,
        ?Adherent $referent
    ): ProcurationProxyCancelledMessage {
        $message = ProcurationProxyCancelledMessage::create($request);
        $message->setReplyTo($this->replyToEmailAddress);

        if ($referent) {
            $message->addBCC($referent->getEmailAddress());
        }

        return $message;
    }

    public function createProxyFoundMessage(ProcurationRequest $request): ProcurationProxyMatchedMessage
    {
        $url = $this->urlGenerator->generate('app_procuration_my_request', [
            'id' => $request->getId(),
            'privateToken' => $request->generatePrivateToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = ProcurationProxyMatchedMessage::create($request, $url);
        $message->setReplyTo($this->replyToEmailAddress);

        return $message;
    }

    /**
     * @param ProcurationRequest[] $requests
     */
    public function createRequestReminderMessage(array $requests): ?ProcurationRequestReminderMessage
    {
        if (!$requests) {
            return null;
        }

        $request = array_shift($requests);

        $message = ProcurationRequestReminderMessage::create($request);
        $message->setReplyTo($this->replyToEmailAddress);

        foreach ($requests as $request) {
            $message->addRecipient(
                $request->getEmailAddress(),
                null,
                ProcurationRequestReminderMessage::createRecipientVariables($request)
            );
        }

        return $message;
    }

    /**
     * @param ProcurationProxy[] $proxies
     */
    public function createProxyReminderMessage(array $proxies): ?ProcurationProxyReminderMessage
    {
        if (!$proxies) {
            return null;
        }

        $proxy = array_shift($proxies);

        $message = ProcurationProxyReminderMessage::create($proxy);
        $message->setReplyTo($this->replyToEmailAddress);

        foreach ($proxies as $proxy) {
            $message->addRecipient(
                $proxy->getEmailAddress(),
                null,
                ProcurationProxyReminderMessage::createRecipientVariables($proxy)
            );
        }

        return $message;
    }

    public function createProxyRegistrationMessage(
        ProcurationProxy $procurationProxy
    ): ProcurationProxyRegistrationConfirmationMessage {
        $message = ProcurationProxyRegistrationConfirmationMessage::create($procurationProxy);
        $message->setReplyTo($this->replyToEmailAddress);

        return $message;
    }

    public function createRequestRegistrationMessage(
        ProcurationRequest $request
    ): ProcurationRequestRegistrationConfirmationMessage {
        $message = ProcurationRequestRegistrationConfirmationMessage::create($request);
        $message->setReplyTo($this->replyToEmailAddress);

        return $message;
    }
}
