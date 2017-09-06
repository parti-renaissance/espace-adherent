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
        $organizer = $event->getOrganizer();

        return new self(
            Uuid::uuid4(),
            '196483',
            $registration->getEmailAddress(),
            self::fixMailjetParsing($firstName),
            'Confirmation de participation à une initiative citoyenne En Marche !',
            static::getTemplateVars(
                $event->getName(),
                $organizer->getFirstName(),
                $organizer->getLastName()
            ),
            static::getRecipientVars($firstName)
        );
    }

    private static function getTemplateVars(
        string $initiativeName,
        string $referentFirstName,
        string $referentLastName
    ): array {
        return [
            'IC_name' => self::escape($initiativeName),
            'IC_organiser_firstname' => self::escape($referentFirstName),
            'IC_organiser_lastname' => self::escape($referentLastName),
        ];
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
