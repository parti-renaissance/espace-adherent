<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventRegistrationConfirmationMessage extends Message
{
    public static function create(EventRegistration $registration, string $eventUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $registration->getFullName(),
            static::getTemplateVars($registration, $eventUrl),
            ['recipient_first_name' => $registration->getFirstName()]
        );
    }

    private static function getTemplateVars(EventRegistration $registration, string $eventUrl): array
    {
        $event = $registration->getEvent();

        return [
            'event_name' => self::escape($event->getName()),
            'event_organizer' => self::escape($event->getOrganizerName()),
            'event_url' => self::urlEncode($eventUrl),
            'event_date' => $event->getBeginAt()->format('d/m/Y'),
            'event_hour' => $event->getBeginAt()->format('H:i'),
            'event_address' => self::escape($event->getInlineFormattedAddress()),
        ];
    }
}
