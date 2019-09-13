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
            'Votre comité sera bientôt en ligne',
            [
                'committee_city' => $city,
            ],
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
            ]
        );
    }
}
