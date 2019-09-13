<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use Ramsey\Uuid\Uuid;

final class EventNotificationMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[] $recipients
     *
     * @return EventNotificationMessage
     */
    public static function create(
        array $recipients,
        Adherent $host,
        Event $event,
        string $eventLink,
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
            static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
            sprintf(
                '%sh%s',
                static::formatDate($event->getLocalBeginAt(), 'HH'),
                static::formatDate($event->getLocalBeginAt(), 'mm')
            ),
            $event->getInlineFormattedAddress(),
            $eventLink,
            $event->getDescription(),
            $event->getCommittee()->getName()
        );

        $message = new static(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            sprintf(
                '%s - %s : Nouvel événement de %s : %s',
                static::formatDate($event->getLocalBeginAt(), 'd MMMM'),
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
        string $eventDate,
        string $eventHour,
        string $eventAddress,
        string $eventLink,
        string $eventDescription,
        string $committeeName
    ): array {
        return [
            // Global common variables
            'animator_firstname' => self::escape($hostFirstName),
            'event_name' => self::escape($eventName),
            'event_date' => $eventDate,
            'event_hour' => $eventHour,
            'event_address' => self::escape($eventAddress),
            'event_slug' => $eventLink,
            'event_description' => $eventDescription,
            'committee_name' => $committeeName,
        ];
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
