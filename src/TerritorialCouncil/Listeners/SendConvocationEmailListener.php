<?php

namespace App\TerritorialCouncil\Listeners;

use App\Mailer\MailerService;
use App\TerritorialCouncil\Convocation\Events;
use App\TerritorialCouncil\Event\ConvocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendConvocationEmailListener implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerService $transactionalMailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CONVOCATION_CREATED => 'onConvocationCreate',
        ];
    }

    public function onConvocationCreate(ConvocationEvent $event): void
    {
    }
}
