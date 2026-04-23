<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Event\Event;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AgoraEventInvitationMessage;
use App\Mailer\Message\Renaissance\RenaissanceEventNotificationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventInvitationNotifier
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

        $link = rtrim($this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL), '/').'/evenements/'.$event->getSlug();

        $message = null !== $event->agora
            ? AgoraEventInvitationMessage::create($event, $event->agora, $adherents, $link)
            : RenaissanceEventNotificationMessage::create($adherents, $event->getAuthor(), $event, $link);

        $this->transactionalMailer->sendMessage($message);
    }
}
