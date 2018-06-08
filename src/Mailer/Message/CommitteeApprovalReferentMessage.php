<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

final class CommitteeApprovalReferentMessage extends Message
{
    public static function create(
        Adherent $referent,
        Adherent $creator,
        Committee $committee,
        string $creatorContactUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $referent->getEmailAddress(),
            $referent->getFullName(),
            static::getTemplateVars($creator, $committee, $creatorContactUrl),
            ['recipient_first_name' => self::escape($referent->getFirstName())]
        );
    }

    private static function getTemplateVars(Adherent $creator, Committee $committee, string $creatorContactUrl): array
    {
        return [
            'committee_name' => $committee->getName(),
            'committee_city' => $committee->getCityName(),
            'creator_first_name' => $creator->getFirstName(),
            'creator_contact_url' => $creatorContactUrl,
        ];
    }
}
