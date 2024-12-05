<?php

namespace App\MyTeam;

use App\Entity\MyTeam\DelegatedAccess;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\DelegatedAccessCreatedMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DelegatedAccessNotifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendNewDelegatedAccessNotification(DelegatedAccess $delegatedAccess): void
    {
        $this->transactionalMailer->sendMessage(
            DelegatedAccessCreatedMessage::create(
                $delegatedAccess,
                $this->urlGenerator->generate('vox_app_redirect'),
            )
        );
    }
}
