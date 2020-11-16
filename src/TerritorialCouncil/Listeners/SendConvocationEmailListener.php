<?php

namespace App\TerritorialCouncil\Listeners;

use App\Mailer\MailerService;
use App\Mailer\Message\ReferentInstanceConvocationMessage;
use App\TerritorialCouncil\Convocation\Events;
use App\TerritorialCouncil\Event\ConvocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendConvocationEmailListener implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerService $transactionalMailer)
    {
        $this->mailer = $transactionalMailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CONVOCATION_CREATED => 'onConvocationCreate',
        ];
    }

    public function onConvocationCreate(ConvocationEvent $event): void
    {
        $convocation = $event->getConvocation();
        $instance = $convocation->getEntity();

        if (!\count($memberships = $instance->getMemberships())) {
            return;
        }

        $this->mailer->sendMessage(ReferentInstanceConvocationMessage::create(
            $event->getConvocation(),
            $convocation->getCreatedBy(),
            $memberships->toArray()
        ));
    }
}
