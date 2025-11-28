<?php

declare(strict_types=1);

namespace App\Renaissance\Newsletter\Handler;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\RenaissanceNewsletterSubscriptionConfirmationMessage;
use App\Renaissance\Newsletter\Command\SendWelcomeMailCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendWelcomeMailCommandHandler
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
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
        return RenaissanceNewsletterSubscriptionConfirmationMessage::create(
            $subscription->email,
            $this->urlGenerator->generate('app_renaissance_newsletter_confirm', [
                'uuid' => $subscription->getUuid()->toString(),
                'confirm_token' => $subscription->token->toString(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }
}
