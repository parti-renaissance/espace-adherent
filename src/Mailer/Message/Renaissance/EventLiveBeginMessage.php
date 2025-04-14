<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use Ramsey\Uuid\Uuid;

final class EventLiveBeginMessage extends AbstractRenaissanceMessage
{
    /** @param Adherent[] $recipients */
    public static function create(array $recipients, Event $event, string $eventLink): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Début de live',
            static::getTemplateVars($event, $eventLink),
            self::getRecipientVars($recipient)
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                self::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(Event $event, string $eventLink): array
    {
        return [
            'event_name' => $event->getName(),
            'event_slug' => $eventLink,
            'event_description' => $event->getDescription(),
        ];
    }

    private static function getRecipientVars(Adherent $adherent): array
    {
        return [
            'target_firstname' => self::escape($adherent->getFirstName()),
        ];
    }
}
