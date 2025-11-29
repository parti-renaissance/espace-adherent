<?php

namespace App\Adherent\Handler;

use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AdherentResubscribeEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[AsMessageHandler]
class SendResubscribeEmailCommandHandler
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function __invoke(SendResubscribeEmailCommand $command): void
    {
        $adherent = $command->adherent;

        $link = $this->loginLinkHandler->createLoginLink($adherent, targetPath: '/app?state='.urlencode('/profil/communications?autorun=1'))->getUrl();

        $this->transactionalMailer->sendMessage(AdherentResubscribeEmailMessage::create($adherent, $link));
    }
}
