<?php

namespace AppBundle\EventListener;

use AppBundle\Mailer\MailerService;
use AppBundle\Procuration\Event\ProcurationEvents;
use AppBundle\Procuration\Event\ProcurationProxyEvent;
use AppBundle\Procuration\Event\ProcurationRequestEvent;
use AppBundle\Procuration\ProcurationProxyMessageFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProcurationMailerSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $factory;

    public function __construct(MailerService $mailer, ProcurationProxyMessageFactory $factory)
    {
        $this->mailer = $mailer;
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        return [
            ProcurationEvents::REQUEST_REGISTRATION => 'sendProcurationRequestRegistrationEmail',
            ProcurationEvents::REQUEST_PROCESSED => 'sendProcurationProxyFoundEmail',
            ProcurationEvents::REQUEST_UNPROCESSED => 'sendProcurationProxyCancelledEmail',
            ProcurationEvents::PROXY_REGISTRATION => 'sendProcurationProxyRegistrationEmail',
        ];
    }

    public function sendProcurationProxyFoundEmail(ProcurationRequestEvent $event): void
    {
        if ($event->notify()) {
            $this->mailer->sendMessage($this->factory->createProxyFoundMessage($event->getRequest()));
        }
    }

    public function sendProcurationProxyCancelledEmail(ProcurationRequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($event->notify() && $request->hasFoundProxy()) {
            $this->mailer->sendMessage($this->factory->createProxyCancelledMessage($request, $event->getReferent()));
        }
    }

    public function sendProcurationProxyRegistrationEmail(ProcurationProxyEvent $event): void
    {
        $this->mailer->sendMessage($this->factory->createProxyRegistrationMessage($event->getProxy()));
    }

    public function sendProcurationRequestRegistrationEmail(ProcurationRequestEvent $event): void
    {
        $this->mailer->sendMessage($this->factory->createRequestRegistrationMessage($event->getRequest()));
    }
}
