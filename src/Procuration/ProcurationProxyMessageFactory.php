<?php

namespace App\Procuration;

use App\Entity\Adherent;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Mailer\Message\Procuration\ProcurationProxyCancelledMessage;
use App\Mailer\Message\Procuration\ProcurationProxyFoundMessage;
use App\Mailer\Message\Procuration\ProcurationProxyRegistrationConfirmationMessage;
use App\Mailer\Message\Procuration\ProcurationProxyReminderMessage;
use App\Mailer\Message\Procuration\ProcurationRequestRegistrationConfirmationMessage;
use App\Routing\RemoteUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcurationProxyMessageFactory
{
    private $urlGenerator;
    private $replyToEmailAddress;

    public function __construct(RemoteUrlGenerator $urlGenerator, string $replyToEmailAddress)
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

    public function createProxyFoundMessage(ProcurationRequest $request): ProcurationProxyFoundMessage
    {
        $url = $this->urlGenerator->generate('app_procuration_my_request', [
            'id' => $request->getId(),
            'privateToken' => $request->generatePrivateToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = ProcurationProxyFoundMessage::create($request, $url);
        $message->setReplyTo($this->replyToEmailAddress);

        return $message;
    }

    /**
     * @param ProcurationRequest[] $requests
     */
    public function createProxyReminderMessage(array $requests): ?ProcurationProxyReminderMessage
    {
        if (!$requests) {
            return null;
        }

        $request = array_shift($requests);

        $url = $this->urlGenerator->generateRemoteUrl('app_procuration_my_request', [
            'id' => $request->getId(),
            'privateToken' => $request->generatePrivateToken(),
        ]);

        $message = ProcurationProxyReminderMessage::create($request, $url);
        $message->setReplyTo($this->replyToEmailAddress);

        foreach ($requests as $request) {
            $url = $this->urlGenerator->generateRemoteUrl('app_procuration_my_request', [
                'id' => $request->getId(),
                'privateToken' => $request->generatePrivateToken(),
            ]);

            $message->addRecipient(
                $request->getEmailAddress(),
                null,
                ProcurationProxyReminderMessage::createRecipientVariables($request, $url)
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
