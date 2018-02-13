<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventRegistrationConfirmationMessage extends Message
{
    public static function createFromRegistration(EventRegistration $registration, string $eventLink): self
    {
        $event = $registration->getEvent();
        $firstName = $registration->getFirstName();

        return new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $firstName,
            self::getTemplateVars(
                $event->getName(),
                static::formatDate($event->getBeginAt(), 'EEEE d MMMM y'),
                sprintf(
                    '%sh%s',
                    static::formatDate($event->getBeginAt(), 'HH'),
                    static::formatDate($event->getBeginAt(), 'mm')
                ),
                $event->getInlineFormattedAddress(),
                $event->getOrganizerName(),
                $eventLink
            ),
            self::getRecipientVars($firstName)
        );
    }

    private static function getTemplateVars(
        string $eventName,
        string $eventDate,
        string $eventHour,
        string $eventAddress,
        string $organizerName,
        string $eventLink
    ): array {
        return [
            'event_name' => self::escape($eventName),
            'event_date' => $eventDate,
            'event_hour' => $eventHour,
            'event_address' => self::escape($eventAddress),
            'event_organizer' => self::escape($organizerName),
            'event_link' => $eventLink,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
