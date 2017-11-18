<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class CommitteeCreationConfirmationMessage extends Message
{
    public static function create(Adherent $adherent, string $city): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            self::getTemplateVars($city),
            self::getRecipientVars($adherent->getFirstName())
        );
    }

    private static function getTemplateVars(string $committeeCity): array
    {
        return [
            'committee_city' => self::escape($committeeCity),
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
