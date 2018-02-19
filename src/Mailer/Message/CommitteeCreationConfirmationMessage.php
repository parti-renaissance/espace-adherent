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
            static::getTemplateVars($adherent, $committee)
        );
    }

    private static function getTemplateVars(Adherent $adherent, Committee $committee): array
    {
        return [
            'first_name' => self::escape($adherent->getFirstName()),
            'committee_city' => $committee->getCityName(),
        ];
    }
}
