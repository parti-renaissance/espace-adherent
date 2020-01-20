<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenAction;
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
            $registration->getEmailAddress(),
            $firstName,
            'Votre inscription a bien été prise en compte',
            static::getTemplateVars($citizenAction, $calendarEventUrl),
            static::getRecipientVars($firstName)
        );
    }

    private static function getTemplateVars(CitizenAction $citizenAction, string $calendarEventUrl): array
    {
        return [
            'citizen_action_name' => self::escape($citizenAction->getName()),
            'citizen_action_organiser' => self::escape($citizenAction->getOrganizerName()),
            'citizen_action_calendar_url' => self::escape($calendarEventUrl),
            'citizen_action_date' => static::formatDate($citizenAction->getLocalBeginAt(), 'EEEE d MMMM y'),
            'citizen_action_hour' => sprintf(
                '%sh%s',
                static::formatDate($citizenAction->getLocalBeginAt(), 'HH'),
                static::formatDate($citizenAction->getLocalBeginAt(), 'mm')
            ),
            'citizen_action_address' => self::escape($citizenAction->getInlineFormattedAddress()),
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return ['prenom' => self::escape($firstName)];
    }
}
