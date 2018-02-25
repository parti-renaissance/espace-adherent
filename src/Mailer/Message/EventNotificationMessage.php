<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use Ramsey\Uuid\Uuid;

class EventNotificationMessage extends Message
{
    public static function create(
        array $recipients,
        Adherent $host,
        Event $event,
        string $eventShowLink,
        string $eventAttendLink
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
        }

        $message = new static(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($host, $event, $eventShowLink, $eventAttendLink),
            static::getRecipientVars($recipient),
            $host->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(
        Adherent $host,
        Event $event,
        string $eventShowLink,
        string $eventAttendLink
    ): array {
        return [
            'host_first_name' => self::escape($host->getFirstName()),
            'event_name' => self::escape($event->getName()),
            'event_date' => static::formatDate($event->getBeginAt(), 'EEEE d MMMM y'),
            'event_hour' => sprintf(
                '%sh%s',
                static::formatDate($event->getBeginAt(), 'HH'),
                static::formatDate($event->getBeginAt(), 'mm')
            ),
            'event_address' => self::escape($event->getInlineFormattedAddress()),
            'event_show_link' => $eventShowLink,
            'event_attend_link' => $eventAttendLink,
        ];
    }

    private static function getRecipientVars(Adherent $recipient): array
    {
        return [
            'first_name' => self::escape($recipient->getFirstName()),
        ];
    }

    private static function formatDate(\DateTimeInterface $date, string $format): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        );

        return $formatter->format($date);
    }
}
