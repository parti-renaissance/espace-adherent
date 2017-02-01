<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeEvent;
use Ramsey\Uuid\Uuid;

class CommitteeEventNotificationMessage extends MailjetMessage
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[]     $recipients
     * @param Adherent       $host
     * @param CommitteeEvent $event
     * @param string         $eventLink
     * @param \Closure       $recipientVarsGenerator
     *
     * @return CommitteeEventNotificationMessage
     */
    public static function create(
        array $recipients,
        Adherent $host,
        CommitteeEvent $event,
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
            static::formatDate($event->getBeginAt(), 'EEEE d MMMM y'),
            sprintf(
                '%sh%s',
                static::formatDate($event->getBeginAt(), 'HH'),
                static::formatDate($event->getBeginAt(), 'mm')
            ),
            $event->getInlineFormattedAddress(),
            $eventLink
        );

        $message = new self(
            Uuid::uuid4(),
            '61378',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Nouvel événement dans votre comité En Marche !',
            $vars,
            $recipientVarsGenerator($recipient),
            $event->getUuid()
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
        string $eventLink
    ): array {
        return [
            // Global common variables
            'animator_firstname' => $hostFirstName,
            'event_name' => $eventName,
            'event_date' => $eventDate,
            'event_hour' => $eventHour,
            'event_address' => $eventAddress,

            // @todo this variable name must be renamed and uniquified in the template.
            'event_slug' => $eventLink,
            'event-slug' => $eventLink,

            // Recipient specific template variables
            'target_firstname' => '',
            'event_ok_link' => '',
            'event_ko_link' => $eventLink,
        ];
    }

    public static function getRecipientVars(
        string $firstName,
        string $acceptEventAttendanceLink,
        string $declineEventAttendanceLink
    ): array {
        return [
            'target_firstname' => $firstName,
            'event_ok_link' => $acceptEventAttendanceLink,
            'event_ko_link' => $declineEventAttendanceLink,
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
