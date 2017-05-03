<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use Ramsey\Uuid\Uuid;

final class EventInvitationMessage extends MailjetMessage
{
    public static function createFromInvite(EventInvite $invite, Event $event, string $eventUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            '132747',
            $invite->getEmail(),
            self::fixMailjetParsing(self::escape($invite->getFullName())),
            $invite->getFullName().' vous invite à un événement En Marche !',
            [
                'sender_firstname' => self::escape($invite->getFirstName()),
                'sender_message' => self::escape($invite->getMessage()),
                'event_name' => self::escape($event->getName()),
                'event_slug' => $eventUrl,
            ],
            [],
            null,
            $invite->getUuid()
        );

        $message->setReplyTo($invite->getEmail());

        foreach ($invite->getGuests() as $guest) {
            $message->addCC($guest);
        }

        return $message;
    }
}
