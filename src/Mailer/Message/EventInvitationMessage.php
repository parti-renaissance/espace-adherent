<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use Ramsey\Uuid\Uuid;

final class EventInvitationMessage extends Message
{
    public static function create(EventInvite $invite, Event $event, string $eventUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            $invite->getEmail(),
            self::escape($invite->getFullName()),
            static::getTemplateVars($invite, $event, $eventUrl),
            [],
            $invite->getEmail()
        );

        foreach ($invite->getGuests() as $guest) {
            $message->addCC($guest);
        }

        return $message;
    }

    private static function getTemplateVars(EventInvite $invite, Event $event, string $eventUrl): array
    {
        return [
            'sender_first_name' => self::escape($invite->getFirstName()),
            'sender_full_name' => self::escape($invite->getFullName()),
            'sender_message' => self::escape($invite->getMessage()),
            'event_name' => self::escape($event->getName()),
            'event_slug' => $eventUrl,
        ];
    }
}
