<?php

declare(strict_types=1);

namespace App\Membership\Signup\Handler;

use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\SignupConfirmationMessage;
use App\Membership\Signup\Command\SendSignupConfirmationCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[AsMessageHandler]
class SendSignupConfirmationCommandHandler
{
    private const MAGIC_LINK_LIFETIME = 86400;

    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function __invoke(SendSignupConfirmationCommand $command): void
    {
        $adherent = $command->adherent;

        $magicLink = $this->loginLinkHandler
            ->createLoginLink($adherent, lifetime: self::MAGIC_LINK_LIFETIME, appCode: $adherent->getSource())
            ->getUrl()
        ;

        $this->transactionalMailer->sendMessage(
            SignupConfirmationMessage::create($adherent, $magicLink)
        );
    }
}
