<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

class CommitteeCreationConfirmationMessage extends Message
{
    public static function create(Adherent $adherent, Committee $committee): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::getTemplateVars($committee),
            static::getRecipientVars($adherent)
        );
    }

    private static function getTemplateVars(Committee $committee): array
    {
        return [
            'committee_city' => $committee->getCityName(),
        ];
    }

    private static function getRecipientVars(Adherent $adherent): array
    {
        return [
            'target_firstname' => self::escape($adherent->getFirstName()),
        ];
    }
}
