<?php

namespace App\Mailer\Message\JeMengage;

use App\Entity\Event\EventRegistration;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class JeMengageEventRegistrationConfirmationMessage extends AbstractJeMengageMessage
{
    public static function createFromRegistration(EventRegistration $registration, string $eventLink): Message
    {
        $event = $registration->getEvent();
        $firstName = $registration->getFirstName();

        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $registration->getEmailAddress(),
            $firstName,
            'Confirmation de participation à un événement',
            static::getTemplateVars(
                $event->getName(),
                $event->getOrganizerName(),
                $eventLink
            ),
            static::getRecipientVars($firstName)
        ));
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
