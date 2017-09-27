<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailer\Message\ProcurationProxyCancelledMessage;
use AppBundle\Mailer\Message\ProcurationProxyFoundMessage;
use AppBundle\Mailer\Message\ProcurationProxyReminderMessage;
use AppBundle\Routing\RemoteUrlGenerator;
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

    public function createProxyCancelledMessage(ProcurationRequest $request, ProcurationProxy $proxy, ?Adherent $procurationManager): ProcurationProxyCancelledMessage
    {
        $message = ProcurationProxyCancelledMessage::create($request, $proxy, $procurationManager);
        $message->setReplyTo($this->replyToEmailAddress);

        return $message;
    }

    public function createProxyFoundMessage(Adherent $procurationManager, ProcurationRequest $request, ProcurationProxy $proxy): ProcurationProxyFoundMessage
    {
        $url = $this->urlGenerator->generate('app_procuration_my_request', [
            'id' => $request->getId(),
            'token' => $request->generatePrivateToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = ProcurationProxyFoundMessage::create($procurationManager, $request, $proxy, $url);
        $message->setReplyTo($this->replyToEmailAddress);

        return $message;
    }

    /**
     * @param ProcurationRequest[] $requests
     *
     * @return ProcurationProxyReminderMessage
     */
    public function createProxyReminderMessage(array $requests): ProcurationProxyReminderMessage
    {
        if (!$requests) {
            return null;
        }

        $request = array_shift($requests);

        $url = $this->urlGenerator->generateRemoteUrl('app_procuration_my_request', [
            'id' => $request->getId(),
            'token' => $request->generatePrivateToken(),
        ]);

        $message = ProcurationProxyReminderMessage::create($request, $url);
        $message->setReplyTo($this->replyToEmailAddress);

        foreach ($requests as $request) {
            $url = $this->urlGenerator->generateRemoteUrl('app_procuration_my_request', [
                'id' => $request->getId(),
                'token' => $request->generatePrivateToken(),
            ]);

            $message->addRecipient(
                $request->getEmailAddress(),
                null,
                ProcurationProxyReminderMessage::createRecipientVariables($request, $url)
            );
        }

        return $message;
    }
}
