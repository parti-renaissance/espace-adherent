<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Event\EventRegistration;
use Ramsey\Uuid\Uuid;

class EventRegistrationConfirmationMessage extends AbstractRenaissanceMessage
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
            [
                'event_name' => self::escape($event->getName()),
                'event_organiser' => self::escape($event->getOrganizerName()),
                'event_link' => $eventLink,
                'visio_url' => $event->getVisioUrl(),
                'live_url' => $event->liveUrl,
            ],
            static::getRecipientVars($firstName)
        );
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'first_name' => self::escape($firstName),
        ];
    }
}
