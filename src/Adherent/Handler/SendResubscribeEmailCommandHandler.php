<?php

declare(strict_types=1);

namespace App\Adherent\Handler;

use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AdherentResubscribeEmailMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[AsMessageHandler]
class SendResubscribeEmailCommandHandler
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendResubscribeEmailCommand $command): void
    {
        $adherent = $command->adherent;

        // Never re-engage an undeliverable (bounced) or spam-complaining address, whatever the dispatch path.
        if ($adherent->isEmailHardBounced() || $adherent->isEmailComplained()) {
            $this->logger->warning('[Resubscribe] Skipped: address is bounced or complained', [
                'adherent' => $adherent->getUuidAsString(),
            ]);

            return;
        }

        $link = $this->loginLinkHandler->createLoginLink($adherent, targetPath: '/app?state='.urlencode('/profil/communications?autorun=1'))->getUrl();

        $this->transactionalMailer->sendMessage(AdherentResubscribeEmailMessage::create($adherent, $link));
    }
}
