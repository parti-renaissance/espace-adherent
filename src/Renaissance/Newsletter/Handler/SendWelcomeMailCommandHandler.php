<?php

namespace App\Renaissance\Newsletter\Handler;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\EuNewsletterSubscriptionConfirmationMessage;
use App\Mailer\Message\Renaissance\RenaissanceNewsletterSubscriptionConfirmationMessage;
use App\Newsletter\NewsletterTypeEnum;
use App\Renaissance\Newsletter\Command\SendWelcomeMailCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendWelcomeMailCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $nationalEventHost,
    ) {
    }

    public function __invoke(SendWelcomeMailCommand $command): void
    {
        $subscription = $command->newsletterSubscription;

        $message = $this->createMessage($subscription);

        $this->transactionalMailer->sendMessage($message);
    }

    private function createMessage(NewsletterSubscription $subscription): Message
    {
        if (NewsletterTypeEnum::SITE_EU === $subscription->source) {
            return EuNewsletterSubscriptionConfirmationMessage::create(
                $subscription->email,
                $this->urlGenerator->generate('app_renaissance_newsletter_confirm', [
                    'uuid' => $subscription->getUuid()->toString(),
                    'confirm_token' => $subscription->token->toString(),
                    'app_domain' => $this->nationalEventHost,
                ], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        return RenaissanceNewsletterSubscriptionConfirmationMessage::create(
            $subscription->email,
            $this->urlGenerator->generate('app_renaissance_newsletter_confirm', [
                'uuid' => $subscription->getUuid()->toString(),
                'confirm_token' => $subscription->token->toString(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }
}
