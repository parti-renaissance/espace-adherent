<?php

namespace App\Mailer\Message\Ensemble;

use App\Entity\Event\EventRegistration;
use Ramsey\Uuid\Uuid;

class EnsembleEventRegistrationConfirmationMessage extends AbstractEnsembleMessage
{
    public static function createFromRegistration(EventRegistration $registration, string $eventLink): self
    {
        $event = $registration->getEvent();
        $firstName = $registration->getFirstName();

        return new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $firstName,
            'Inscription confirmée',
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
            'first_name' => self::escape($firstName),
        ];
    }
}
