<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\ValueObject\Genders;
use Ramsey\Uuid\Uuid;

final class CommitteeCitizenInitiativeNotificationMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[]        $recipients
     * @param CommitteeFeedItem $feedItem
     * @param string            $citizenInitiativeLink
     * @param string            $attendLink
     *
     * @return self
     */
    public static function create(array $recipients, CommitteeFeedItem $feedItem, string $citizenInitiativeLink, string $attendLink): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $citizenInitiative = $feedItem->getEvent();
        if (!$citizenInitiative instanceof BaseEvent) {
            throw new \RuntimeException('Citizen initiative not found for feed item "'.$feedItem->getId().'".');
        }

        $vars = static::getTemplateVars(
            $feedItem->getAuthorFirstName(),
            $citizenInitiative->getName(),
            self::formatDate($citizenInitiative->getBeginAt(), 'EEEE d MMMM y'),
            sprintf(
                '%sh%s',
                self::formatDate($citizenInitiative->getBeginAt(), 'HH'),
                self::formatDate($citizenInitiative->getBeginAt(), 'mm')
            ),
            $citizenInitiative->getInlineFormattedAddress(),
            $citizenInitiativeLink
        );

        $message = new self(
            Uuid::uuid4(),
            '196519',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Des nouvelles de votre comité',
            $vars,
            static::getRecipientVars($recipient->getFirstName()),
            $feedItem->getAuthor()->getEmailAddress()
        );

        $sender = $feedItem->getAuthor()->getFirstName().', ';
        $sender .= Genders::FEMALE === $feedItem->getAuthor()->getGender() ? 'animatrice' : 'animateur';
        $sender .= ' de votre comité';

        $message->setSenderName($sender);

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    private static function getTemplateVars(
        string $hostFirstName,
        string $citizenInitiativeName,
        string $citizenInitiativeDate,
        string $citizenInitiativeHour,
        string $citizenInitiativeAddress,
        string $citizenInitiativeLink
    ): array {
        return [
            'animator_firstname' => self::escape($hostFirstName),
            'IC_name' => self::escape($citizenInitiativeName),
            'IC_date' => $citizenInitiativeDate,
            'IC_hour' => $citizenInitiativeHour,
            'IC_address' => self::escape($citizenInitiativeAddress),
            'IC_slug' => $citizenInitiativeLink,
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
