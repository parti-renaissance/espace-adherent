<?php

namespace App\Newsletter\Listener;

use App\Mailer\MailerService;
use App\Mailer\Message\NewsletterAdherentSubscriptionMessage;
use App\Mailer\Message\NewsletterSubscriptionConfirmationMessage;
use App\Newsletter\Events;
use App\Newsletter\NewsletterEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendWelcomeMailSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;
    private $logger;

    public function __construct(MailerService $mailer, UrlGeneratorInterface $urlGenerator, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::SUBSCRIBE => 'sendEmailValidation',
            Events::NOTIFICATION => 'sendAdherentSubscriptionEmail',
        ];
    }

    public function sendEmailValidation(NewsletterEvent $event): void
    {
        $subscription = $event->getNewsletter();
        if (null === $subscription->getUuid() || null === $subscription->getToken()) {
            $this->logger->error(\sprintf(
                'NewsletterSubscription with id "%d" has no UUID or token to create a confirmation link.',
                $subscription->getId()
            ));

            return;
        }

        $params = [
            'uuid' => (string) $subscription->getUuid(),
            'token' => (string) $subscription->getToken(),
        ];

        $activationUrl = $this->urlGenerator->generate(
            'app_newsletter_confirmation',
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailer->sendMessage(
            NewsletterSubscriptionConfirmationMessage::create($event->getNewsletter(), $activationUrl)
        );
    }

    public function sendAdherentSubscriptionEmail(NewsletterEvent $event): void
    {
        $emailNotificationsUrl = $this->generateEmailNotificationsUrl();

        $this->mailer->sendMessage(NewsletterAdherentSubscriptionMessage::create($event->getAdherent(), $emailNotificationsUrl));
    }

    private function generateEmailNotificationsUrl(): string
    {
        return $this->urlGenerator->generate(
            'app_user_set_email_notifications',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
