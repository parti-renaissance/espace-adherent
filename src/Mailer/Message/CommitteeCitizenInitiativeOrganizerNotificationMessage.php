<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use Ramsey\Uuid\Uuid;

final class CommitteeCitizenInitiativeOrganizerNotificationMessage extends Message
{
    public static function create(Adherent $recipient, CommitteeFeedItem $feedItem, string $contactLink): self
    {
        $message = new self(
            Uuid::uuid4(),
            '196522',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Votre initiative citoyenne a été partagée',
            static::getTemplateVars(
                $feedItem->getAuthor()->getFirstName(),
                $feedItem->getAuthor()->getLastName(),
                $contactLink,
                $feedItem->getCommittee()->getName(),
                $feedItem->getEvent()->getName()
            ),
            static::getRecipientVars($recipient->getFirstName())
        );

        return $message;
    }

    private static function getTemplateVars(
        string $referentFirstName,
        string $referentLastName,
        string $contactLink,
        string $committeeName,
        string $initiativeName
    ): array {
        return [
            'animator_firstname' => self::escape($referentFirstName),
            'animator_lastname' => self::escape($referentLastName),
            'animator_contact_link' => $contactLink,
            'committee_name' => self::escape($committeeName),
            'IC_name' => $initiativeName,
        ];
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
