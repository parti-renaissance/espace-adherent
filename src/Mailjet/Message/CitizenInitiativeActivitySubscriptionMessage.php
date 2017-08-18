<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use Ramsey\Uuid\Uuid;

final class CitizenInitiativeActivitySubscriptionMessage extends MailjetMessage
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[]        $recipients
     * @param Adherent          $organizer
     * @param CitizenInitiative $citizenInitiative
     * @param string            $citizenInitiativeLink
     * @param \Closure          $recipientVarsGenerator
     *
     * @return CitizenInitiativeActivitySubscriptionMessage
     */
    public static function create(
        array $recipients,
        Adherent $organizer,
        CitizenInitiative $citizenInitiative,
        string $citizenInitiativeLink,
        \Closure $recipientVarsGenerator
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $vars = self::getTemplateVars(
            $organizer->getFirstName(),
            $organizer->getLastName(),
            $citizenInitiative->getName(),
            self::formatDate($citizenInitiative->getBeginAt(), 'EEEE d MMMM y'),
            sprintf(
                '%sh%s',
                self::formatDate($citizenInitiative->getBeginAt(), 'HH'),
                self::formatDate($citizenInitiative->getBeginAt(), 'mm')
            ),
            $citizenInitiative->getInlineFormattedAddress(),
            $citizenInitiativeLink,
            $citizenInitiative->getSlug()
        );

        $message = new static(
            Uuid::uuid4(),
            '196480',
            $recipient->getEmailAddress(),
            self::fixMailjetParsing($recipient->getFullName()),
            sprintf(
                '%s - %s : Nouvelle initiative citoyenne : %s',
                self::formatDate($citizenInitiative->getBeginAt(), 'd MMMM'),
                $vars['IC_hour'],
                $vars['IC_name']
            ),
            $vars,
            $recipientVarsGenerator($recipient),
            $organizer->getEmailAddress()
        );

        /* @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                self::fixMailjetParsing($recipient->getFullName()),
                $recipientVarsGenerator($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(
        string $organizerFirstName,
        string $organizerLastName,
        string $citizenInitiativeName,
        string $citizenInitiativeDate,
        string $citizenInitiativeHour,
        string $citizenInitiativeAddress,
        string $citizenInitiativeLink,
        string $citizenInitiativeSlug
    ): array {
        return [
            // Global common variables
            'IC_organizer_firstname' => self::escape($organizerFirstName),
            'IC_organizer_lastname' => self::escape($organizerLastName),
            'IC_name' => self::escape($citizenInitiativeName),
            'IC_date' => $citizenInitiativeDate,
            'IC_hour' => $citizenInitiativeHour,
            'IC_address' => self::escape($citizenInitiativeAddress),
            'IC_slug' => $citizenInitiativeSlug,
            'IC_link' => $citizenInitiativeLink,
            // Recipient specific template variables
            'target_firstname' => '',
        ];
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
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
