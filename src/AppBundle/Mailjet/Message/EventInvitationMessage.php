<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\EventInvite;
use Ramsey\Uuid\Uuid;

final class EventInvitationMessage extends MailjetMessage
{
    public static function createFromInvite(EventInvite $invite, string $eventName, string $eventUrl): self
    {
        return new self(
            Uuid::uuid4(),
            '132747',
            $invite->getEmail(),
            null,
            'Rejoins cet événement En Marche !',
            static::getTemplateVars($invite, $eventName, $eventUrl),
            [],
            null,
            $invite->getUuid()
        );
    }

    private static function getTemplateVars(EventInvite $invite, string $eventName, string $eventUrl)
    {
        return [
            'sender_firstname' => self::escape($invite->getFirstName()),
            'sender_message' => self::escape($invite->getMessage()),
            'event_name' => self::escape($eventName),
            'event_slug' => $eventUrl,
        ];
    }
}
