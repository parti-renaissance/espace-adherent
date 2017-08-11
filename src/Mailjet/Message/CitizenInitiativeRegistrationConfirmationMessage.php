<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenInitiativeRegistrationConfirmationMessage extends MailjetMessage
{
    public static function createFromRegistration(EventRegistration $registration): self
    {
        $event = $registration->getEvent();
        $firstName = $registration->getFirstName();

        return new self(
            Uuid::uuid4(),
            '196483',
            $registration->getEmailAddress(),
            self::fixMailjetParsing($firstName),
            'Confirmation de participation à une initiative citoyenne En Marche !',
            [
                'prenom' => self::escape($firstName),
                'IC_name' => self::escape($event->getName()),
                'IC_organiser_firstname' => self::escape($event->getOrganizerName()),
            ]
        );
    }
}
