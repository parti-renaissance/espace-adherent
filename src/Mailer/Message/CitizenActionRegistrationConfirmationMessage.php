<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenActionRegistrationConfirmationMessage extends Message
{
    public static function createFromRegistration(EventRegistration $registration, string $calendarEventUrl): self
    {
        $firstName = $registration->getFirstName();
        $citizenAction = $registration->getEvent();

        return new self(
            Uuid::uuid4(),
            '270978',
            $registration->getEmailAddress(),
            $firstName,
            'Votre inscription a bien été prise en compte',
            static::getTemplateVars(
                $citizenAction->getName(),
                $citizenAction->getOrganizerName(),
                $calendarEventUrl
            ),
            static::getRecipientVars($firstName)
        );
    }

    private static function getTemplateVars(string $eventName, string $organizerName, string $calendarEventUrl): array
    {
        return [
            'citizen_action_name' => self::escape($eventName),
            'citizen_action_organiser' => self::escape($organizerName),
            'citizen_action_calendar_url' => self::escape($calendarEventUrl),
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
