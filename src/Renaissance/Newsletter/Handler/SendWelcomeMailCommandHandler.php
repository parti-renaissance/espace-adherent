<?php

declare(strict_types=1);

namespace App\Renaissance\Newsletter\Handler;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\RenaissanceNewsletterSubscriptionConfirmationMessage;
use App\Renaissance\Newsletter\Command\SendWelcomeMailCommand;
use App\Repository\Renaissance\NewsletterSourceRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendWelcomeMailCommandHandler
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly NewsletterSourceRepository $sourceRepository,
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
        $template = $subscription->source
            ? $this->sourceRepository->findOneByCode($subscription->source)?->confirmationEmailTemplate
            : null;

        return RenaissanceNewsletterSubscriptionConfirmationMessage::create(
            $subscription->email,
            $this->urlGenerator->generate('app_renaissance_newsletter_confirm', [
                'uuid' => $subscription->getUuid()->toRfc4122(),
                'confirm_token' => $subscription->token->toRfc4122(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            $template,
        );
    }
}
