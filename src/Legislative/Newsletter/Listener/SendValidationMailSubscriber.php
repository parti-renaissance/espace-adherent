<?php

namespace App\Legislative\Newsletter\Listener;

use App\Legislative\Newsletter\Events;
use App\Legislative\Newsletter\LegislativeNewsletterEvent;
use App\Mailer\MailerService;
use App\Mailer\Message\LegislativeNewsletterSubscriptionConfirmationMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendValidationMailSubscriber implements EventSubscriberInterface
{
    private MailerService $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private LoggerInterface $logger;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [Events::NEWSLETTER_SUBSCRIBE => 'sendValidationEmail'];
    }

    public function sendValidationEmail(LegislativeNewsletterEvent $event): void
    {
        $subscription = $event->getNewsletter();
        if (null === $subscription->getUuid() || null === $subscription->getToken()) {
            $this->logger->error(sprintf(
                'LegislativeNewsletterSubscription with id "%d" has no UUID or token to create a confirmation link.',
                $subscription->getId()
            ));

            return;
        }

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
