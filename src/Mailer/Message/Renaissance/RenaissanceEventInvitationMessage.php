<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Event\Event;
use App\Entity\Event\EventInvite;
use Ramsey\Uuid\Uuid;

class RenaissanceEventInvitationMessage extends AbstractRenaissanceMessage
{
    public static function createFromInvite(EventInvite $invite, Event $event, string $eventUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            $invite->getEmail(),
            self::escape($invite->getFullName()),
            $invite->getFullName().' vous invite à un événement Renaissance',
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
