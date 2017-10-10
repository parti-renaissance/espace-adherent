<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

final class CommitteeApprovalReferentMessage extends Message
{
    public static function create(
        Adherent $referent,
        Adherent $animator,
        Committee $committee,
        string $contactLink
    ): self {
        return new self(
            Uuid::uuid4(),
            $referent->getEmailAddress(),
            $referent->getFullName(),
            static::getTemplateVars(
                $committee->getName(),
                $committee->getCityName(),
                $animator->getFirstName(),
                $contactLink
            ),
            static::getRecipientVars($referent->getFirstName())
        );
    }

    private static function getTemplateVars(
        string $committeeName,
        string $committeeCityName,
        string $animatorFirstName,
        string $animatorContactLink
    ): array {
        return [
            'committee_name' => $committeeName,
            'committee_city' => $committeeCityName,
            'animator_firstname' => $animatorFirstName,
            'animator_contact_link' => $animatorContactLink,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
