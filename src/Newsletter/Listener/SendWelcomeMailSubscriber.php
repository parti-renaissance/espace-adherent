<?php

namespace AppBundle\Newsletter\Listener;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\NewsletterSubscriptionMessage;
use AppBundle\Newsletter\Events;
use AppBundle\Newsletter\NewsletterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendWelcomeMailSubscriber implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::SUBSCRIBE => 'sendWelcomeMail',
        ];
    }

    public function sendWelcomeMail(NewsletterEvent $event): void
    {
        $this->mailer->sendMessage(
            NewsletterSubscriptionMessage::createFromSubscription($event->getNewsletter())
        );
    }
}
