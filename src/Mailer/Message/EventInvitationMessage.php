<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use Ramsey\Uuid\Uuid;

final class EventInvitationMessage extends Message
{
    public static function createFromInvite(EventInvite $invite, Event $event, string $eventUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            $invite->getEmail(),
            self::escape($invite->getFullName()),
            $invite->getFullName().' vous invite à un événement En Marche !',
            [
                'sender_firstname' => self::escape($invite->getFirstName()),
                'sender_message' => self::escape($invite->getMessage()),
                'event_name' => self::escape($event->getName()),
                'event_slug' => $eventUrl,
            ]
        );

        $message->setReplyTo($invite->getEmail());

        foreach ($invite->getGuests() as $guest) {
            $message->addCC($guest);
        }

        return $message;
    }
}
