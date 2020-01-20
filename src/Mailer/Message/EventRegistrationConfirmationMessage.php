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
            'Confirmation de participation à un événement En Marche !',
            static::getTemplateVars(
                $event->getName(),
                $event->getOrganizerName(),
                $eventLink
            ),
            static::getRecipientVars($firstName)
        );
    }

    private static function getTemplateVars(string $eventName, string $organizerName, string $eventLink): array
    {
        return [
            'event_name' => self::escape($eventName),
            'event_organiser' => self::escape($organizerName),
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
