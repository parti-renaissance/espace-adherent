<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use Ramsey\Uuid\Uuid;

final class CitizenInitiativeOrganizerValidationMessage extends MailjetMessage
{
    public static function create(Adherent $recipient, CitizenInitiative $initiative, string $citizenInitiativeLink): self
    {
        return new self(
            Uuid::uuid4(),
            '196469',
            $recipient->getEmailAddress(),
            self::fixMailjetParsing($recipient->getFullName()),
            'Validation de votre initiative citoyenne',
            [
                'prenom' => self::escape($recipient->getFirstName()),
                'IC_name' => self::escape($initiative->getName()),
                'IC_date' => self::formatDate($initiative->getBeginAt(), 'EEEE d MMMM y'),
                'IC_hour' => sprintf(
                    '%sh%s',
                    self::formatDate($initiative->getBeginAt(), 'HH'),
                    self::formatDate($initiative->getBeginAt(), 'mm')
                ),
                'IC_address' => self::escape($initiative->getInlineFormattedAddress()),
                'IC_slug' => self::escape($initiative->getSlug()),
                'IC_link' => $citizenInitiativeLink,
            ]
        );
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
