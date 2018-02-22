<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventRegistrationConfirmationMessage extends Message
{
    public static function createFromRegistration(EventRegistration $registration, string $eventLink): self
    {
        return new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $registration->getFullName(),
            static::getTemplateVars($registration, $eventLink),
            static::getRecipientVars($registration)
        );
    }

    private static function getTemplateVars(EventRegistration $registration, string $eventLink): array
    {
        $event = $registration->getEvent();

        return [
            'event_name' => self::escape($event->getName()),
            'event_organiser' => self::escape($event->getOrganizerName()),
            'event_link' => $eventLink,
        ];
    }

    private static function getRecipientVars(EventRegistration $registration): array
    {
        return [
            'target_firstname' => self::escape($registration->getFirstName()),
        ];
    }
}
