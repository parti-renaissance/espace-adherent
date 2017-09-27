<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use Ramsey\Uuid\Uuid;

final class CitizenInitiativeOrganizerValidationMessage extends Message
{
    public static function create(Adherent $recipient, CitizenInitiative $initiative, string $citizenInitiativeLink): self
    {
        return new self(
            Uuid::uuid4(),
            '196469',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Validation de votre initiative citoyenne',
            self::getTemplateVars(
                $initiative->getName(),
                self::formatDate($initiative->getBeginAt(), 'EEEE d MMMM y'),
                sprintf(
                    '%sh%s',
                    self::formatDate($initiative->getBeginAt(), 'HH'),
                    self::formatDate($initiative->getBeginAt(), 'mm')
                ),
                $initiative->getInlineFormattedAddress()
            ),
            self::getRecipientVars($recipient->getFirstName())
        );
    }

    private static function getTemplateVars(
        string $citizenInitiativeName,
        string $citizenInitiativeDate,
        string $citizenInitiativeHour,
        string $citizenInitiativeAddress
    ): array {
        return [
            'IC_name' => self::escape($citizenInitiativeName),
            'IC_date' => $citizenInitiativeDate,
            'IC_hour' => $citizenInitiativeHour,
            'IC_address' => self::escape($citizenInitiativeAddress),
        ];
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }

    private static function formatDate(\DateTime $date, string $format): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        );

        return $formatter->format($date);
    }
}
