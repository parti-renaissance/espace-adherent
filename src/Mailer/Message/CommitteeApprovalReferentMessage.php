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
        string $animatorContactLink
    ): self {
        return new self(
            Uuid::uuid4(),
            $referent->getEmailAddress(),
            $referent->getFullName(),
            static::getTemplateVars($animator, $committee, $animatorContactLink),
            static::getRecipientVars($referent)
        );
    }

    private static function getTemplateVars(
        Adherent $animator,
        Committee $committee,
        string $animatorContactLink
    ): array {
        return [
            'committee_name' => $committee->getName(),
            'committee_city' => $committee->getCityName(),
            'animator_firstname' => $animator->getFirstName(),
            'animator_contact_link' => $animatorContactLink,
        ];
    }

    private static function getRecipientVars(Adherent $referent): array
    {
        return [
            'target_firstname' => self::escape($referent->getFirstName()),
        ];
    }
}
