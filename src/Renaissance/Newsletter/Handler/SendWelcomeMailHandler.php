<?php

namespace App\Renaissance\Newsletter\Handler;

use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceNewsletterSubscriptionConfirmationMessage;
use App\Renaissance\Newsletter\Command\SendWelcomeMailCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendWelcomeMailHandler implements MessageHandlerInterface
{
    private MailerService $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(MailerService $transactionalMailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(SendWelcomeMailCommand $command): void
    {
        $subscription = $command->newsletterSubscription;
        $this->mailer->sendMessage(RenaissanceNewsletterSubscriptionConfirmationMessage::create(
            $subscription->email,
            $this->urlGenerator->generate('app_renaissance_newsletter_confirm', [
                'uuid' => $subscription->getUuid()->toString(),
                'confirm_token' => $subscription->token->toString(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}
