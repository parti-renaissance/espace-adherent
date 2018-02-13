<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use Ramsey\Uuid\Uuid;

class EventNotificationMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[] $recipients
     * @param Adherent   $host
     * @param Event      $event
     * @param string     $eventLink
     *
     * @return EventNotificationMessage
     */
    public static function create(
        array $recipients,
        Adherent $host,
        Event $event,
        string $eventLink
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $message = new static(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            self::getTemplateVars(
                $host->getFirstName(),
                $event->getName(),
                $event->getDescription(),
                static::formatDate($event->getBeginAt(), 'EEEE d MMMM y'),
                sprintf(
                    '%sh%s',
                    static::formatDate($event->getBeginAt(), 'HH'),
                    static::formatDate($event->getBeginAt(), 'mm')
                ),
                $event->getInlineFormattedAddress(),
                $eventLink
            ),
            self::getRecipientVars($recipient->getFirstName()),
            $host->getEmailAddress()
        );

        /* @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                self::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    private static function getTemplateVars(
        string $hostFirstName,
        string $eventName,
        string $eventDescription,
        string $eventDate,
        string $eventHour,
        string $eventAddress,
        string $eventLink
    ): array {
        return [
            'animator_firstname' => self::escape($hostFirstName),
            'event_name' => self::escape($eventName),
            'event_description' => self::escape($eventDescription),
            'event_date' => $eventDate,
            'event_hour' => $eventHour,
            'event_address' => self::escape($eventAddress),
            'event_slug' => $eventLink,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
