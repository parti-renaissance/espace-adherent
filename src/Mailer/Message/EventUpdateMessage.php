<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventUpdateMessage extends Message
{
    public static function create(
        array $recipients,
        Adherent $host,
        Event $event,
        string $eventUrl,
        string $icalEventUrl
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, \get_class($recipient)));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName().' '.$recipient->getLastName(),
            'Un événement auquel vous participez a été mis à jour',
            static::getTemplateVars($event, $eventUrl, $icalEventUrl),
            static::getRecipientVars($recipient),
            $host->getEmailAddress()
        );

        /* @var EventRegistration[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName().' '.$recipient->getLastName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(Event $event, string $eventUrl, string $icalEventUrl): array
    {
        return [
            'event_name' => self::escape($event->getName()),
            'event_url' => $eventUrl,
            'event_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
            'event_hour' => sprintf(
                '%sh%s',
                static::formatDate($event->getLocalBeginAt(), 'HH'),
                static::formatDate($event->getLocalBeginAt(), 'mm')
            ),
            'event_address' => $event->getInlineFormattedAddress(),
            'calendar_url' => $icalEventUrl,
        ];
    }

    private static function getRecipientVars(EventRegistration $recipient): array
    {
        return ['first_name' => self::escape($recipient->getFirstName())];
    }
}
