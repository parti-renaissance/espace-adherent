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
     * @param string     $eventOkLink
     * @param \Closure   $recipientVarsGenerator
     *
     * @return EventNotificationMessage
     */
    public static function create(
        array $recipients,
        Adherent $host,
        Event $event,
        string $eventLink,
        string $eventOkLink,
        \Closure $recipientVarsGenerator
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $vars = static::getTemplateVars(
            $host->getFirstName(),
            $event->getName(),
            $event->getDesription(),
            static::formatDate($event->getBeginAt(), 'EEEE d MMMM y'),
            sprintf(
                '%sh%s',
                static::formatDate($event->getBeginAt(), 'HH'),
                static::formatDate($event->getBeginAt(), 'mm')
            ),
            $event->getInlineFormattedAddress(),
            $eventLink,
            $eventOkLink
        );

        $message = new static(
            Uuid::uuid4(),
            '54917',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            sprintf(
                '%s - %s : Nouvel événement de %s : %s',
                static::formatDate($event->getBeginAt(), 'd MMMM'),
                $vars['event_hour'],
                $event->getCommittee()->getName(),
                $vars['event_name']
            ),
            $vars,
            $recipientVarsGenerator($recipient),
            $host->getEmailAddress()
        );

        /* @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                $recipientVarsGenerator($recipient)
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
        string $eventLink,
        string $eventOkLink
    ): array {
        return [
            // Global common variables
            'animator_firstname' => self::escape($hostFirstName),
            'event_name' => self::escape($eventName),
            'event_description' => self::escape($eventDescription),
            'event_date' => $eventDate,
            'event_hour' => $eventHour,
            'event_address' => self::escape($eventAddress),
            'event_slug' => $eventLink,
            'event-slug' => $eventLink,
            'event_ok_link' => $eventOkLink,
            'event_ko_link' => $eventLink,

            // Recipient specific template variables
            'target_firstname' => '',
        ];
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }

    private static function formatDate(\DateTime $date, string $format): string
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
