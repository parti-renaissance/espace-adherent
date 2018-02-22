<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventRegistrationConfirmationMessage extends Message
{
    public static function create(EventRegistration $registration, string $eventLink): self
    {
        return new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $registration->getFullName(),
            static::getTemplateVars($registration, $eventLink)
        );
    }

    private static function getTemplateVars(EventRegistration $registration, string $eventLink): array
    {
        $event = $registration->getEvent();

        return [
            'first_name' => self::escape($registration->getFirstName()),
            'event_name' => self::escape($event->getName()),
            'event_organizer' => self::escape($event->getOrganizerName()),
            'event_link' => $eventLink,
        ];
    }
}
