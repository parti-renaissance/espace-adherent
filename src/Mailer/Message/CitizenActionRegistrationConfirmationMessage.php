<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenActionRegistrationConfirmationMessage extends Message
{
    public static function create(EventRegistration $registration, string $calendarEventUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $registration->getFullName(),
            static::getTemplateVars($registration, $calendarEventUrl)
        );
    }

    private static function getTemplateVars(EventRegistration $registration, string $calendarEventUrl): array
    {
        $citizenAction = $registration->getEvent();

        return [
            'first_name' => self::escape($registration->getFirstName()),
            'citizen_action_name' => self::escape($citizenAction->getName()),
            'citizen_action_organiser' => self::escape($citizenAction->getOrganizerName()),
            'citizen_action_calendar_url' => self::escape($calendarEventUrl),
        ];
    }
}
