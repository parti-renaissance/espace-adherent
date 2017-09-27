<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenInitiativeRegistrationConfirmationMessage extends Message
{
    public static function createFromRegistration(EventRegistration $registration, string $citizenInitiativeLink): self
    {
        $event = $registration->getEvent();
        $firstName = $registration->getFirstName();
        $organizer = $event->getOrganizer();

        return new self(
            Uuid::uuid4(),
            '212744',
            $registration->getEmailAddress(),
            $firstName,
            'Confirmation de participation à une initiative citoyenne En Marche !',
            static::getTemplateVars(
                $event->getName(),
                $organizer->getFirstName(),
                $organizer->getLastName(),
                $citizenInitiativeLink
            ),
            static::getRecipientVars($firstName)
        );
    }

    private static function getTemplateVars(
        string $initiativeName,
        string $referentFirstName,
        string $referentLastName,
        string $citizenInitiativeLink
    ): array {
        return [
            'IC_name' => self::escape($initiativeName),
            'IC_organiser_firstname' => self::escape($referentFirstName),
            'IC_organiser_lastname' => self::escape($referentLastName),
            'IC_link' => $citizenInitiativeLink,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
