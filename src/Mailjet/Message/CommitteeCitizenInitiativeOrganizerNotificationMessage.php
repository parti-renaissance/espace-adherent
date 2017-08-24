<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use Ramsey\Uuid\Uuid;

final class CommitteeCitizenInitiativeOrganizerNotificationMessage extends MailjetMessage
{
    public static function create(Adherent $recipient, CommitteeFeedItem $feedItem, string $contactLink): self
    {
        $message = new self(
            Uuid::uuid4(),
            '196522',
            $recipient->getEmailAddress(),
            self::fixMailjetParsing($recipient->getFullName()),
            'Votre initiative citoyenne a été partagée',
            static::getTemplateVars(
                $feedItem->getAuthor()->getFirstName(),
                $feedItem->getAuthor()->getLastName(),
                $contactLink,
                $feedItem->getCommittee()->getName(),
                $recipient->getFirstName()
            )
        );

        return $message;
    }

    private static function getTemplateVars(
        string $referentFirstName,
        string $referentLastName,
        string $contactLink,
        string $committeeName,
        string $targetFirstName
    ): array {
        return [
            'referent_firstname' => self::escape($referentFirstName),
            'referent_lastname' => self::escape($referentLastName),
            'referent_contact_link' => $contactLink,
            'committee_name' => self::escape($committeeName),
            'target_firstname' => self::escape($targetFirstName),
        ];
    }
}
