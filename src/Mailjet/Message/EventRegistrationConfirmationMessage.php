<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventRegistrationConfirmationMessage extends MailjetMessage
{
    public static function createFromRegistration(EventRegistration $registration): self
    {
        $event = $registration->getEvent();
        $firstName = $registration->getFirstName();

        return new self(
            Uuid::uuid4(),
            '118620',
            $registration->getEmailAddress(),
            self::fixMailjetParsing($firstName),
            'Confirmation de participation à un événement En Marche !',
            [
                'prenom' => self::escape($firstName),
                'event_name' => self::escape($event->getName()),
                'event_organiser' => self::escape($event->getOrganizerName()),
            ]
        );
    }
}
