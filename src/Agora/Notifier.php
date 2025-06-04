<?php

namespace App\Agora;

use App\Entity\Event\Event;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AgoraEventInvitationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Notifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendEventInvitation(Event $event, array $adherents): void
    {
        if (empty($adherents)) {
            return;
        }

        $this->transactionalMailer->sendMessage(AgoraEventInvitationMessage::create(
            $event,
            $event->agora,
            $adherents,
            rtrim($this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL), '/').'/evenements/'.$event->getSlug()
        ));
    }
}
