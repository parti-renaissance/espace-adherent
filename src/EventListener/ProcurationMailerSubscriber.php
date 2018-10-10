<?php

namespace AppBundle\EventListener;

use AppBundle\Mail\Transactional\ProcurationProxyCancelledMail;
use AppBundle\Mail\Transactional\ProcurationProxyFoundMail;
use AppBundle\Procuration\Event\ProcurationEvents;
use AppBundle\Procuration\Event\ProcurationRequestEvent;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcurationMailerSubscriber implements EventSubscriberInterface
{
    private $mailPost;
    private $urlGenerator;
    private $replyToEmailAddress;

    public function __construct(
        MailPostInterface $mailPost,
        UrlGeneratorInterface $urlGenerator,
        string $replyToEmailAddress
    ) {
        $this->mailPost = $mailPost;
        $this->urlGenerator = $urlGenerator;
        $this->replyToEmailAddress = $replyToEmailAddress;
    }

    public static function getSubscribedEvents()
    {
        return [
            ProcurationEvents::REQUEST_PROCESSED => 'sendProcurationProxyFoundEmail',
            ProcurationEvents::REQUEST_UNPROCESSED => 'sendProcurationProxyCancelledEmail',
        ];
    }

    public function sendProcurationProxyFoundEmail(ProcurationRequestEvent $event): void
    {
        if ($event->notify()) {
            $request = $event->getRequest();

            $this->mailPost->address(
                ProcurationProxyFoundMail::class,
                ProcurationProxyFoundMail::createRecipient($request),
                ProcurationProxyFoundMail::createReplyToFromEmail($this->replyToEmailAddress),
                ProcurationProxyFoundMail::createTemplateVars(
                    $request,
                    $this->urlGenerator->generate('app_procuration_my_request', [
                        'id' => $request->getId(),
                        'token' => $request->generatePrivateToken(),
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ),
                ProcurationProxyFoundMail::SUBJECT,
                ProcurationProxyFoundMail::createSender(),
                ProcurationProxyFoundMail::createCcRecipients($request->getFoundProxy())
            );
        }
    }

    public function sendProcurationProxyCancelledEmail(ProcurationRequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($event->notify() && $request->hasFoundProxy()) {
            $this->mailPost->address(
                ProcurationProxyCancelledMail::class,
                ProcurationProxyCancelledMail::createRecipient($request),
                ProcurationProxyCancelledMail::createReplyToFromEmail($this->replyToEmailAddress),
                ProcurationProxyCancelledMail::createTemplateVars($request),
                ProcurationProxyCancelledMail::SUBJECT,
                ProcurationProxyCancelledMail::createSender(),
                ProcurationProxyCancelledMail::createCcRecipients($request->getFoundProxy())
            );
        }
    }
}
