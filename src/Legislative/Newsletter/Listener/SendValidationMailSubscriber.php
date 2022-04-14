<?php

namespace App\Legislative\Newsletter\Listener;

use App\Legislative\Newsletter\Events;
use App\Legislative\Newsletter\LegislativeNewsletterEvent;
use App\Mailer\MailerService;
use App\Mailer\Message\Legislatives\LegislativeNewsletterSubscriptionConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendValidationMailSubscriber implements EventSubscriberInterface
{
    private MailerService $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(MailerService $transactionalMailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [Events::NEWSLETTER_SUBSCRIBE => 'sendValidationEmail'];
    }

    public function sendValidationEmail(LegislativeNewsletterEvent $event): void
    {
        $subscription = $event->getNewsletter();

        $confirmationLink = $this->urlGenerator->generate(
            'app_legislatives_newsletter_confirmation',
            [
                'uuid' => (string) $subscription->getUuid(),
                'validation_token' => (string) $subscription->getToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailer->sendMessage(
            LegislativeNewsletterSubscriptionConfirmationMessage::create($subscription, $confirmationLink)
        );
    }
}
