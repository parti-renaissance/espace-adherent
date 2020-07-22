<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Committee;
use Ramsey\Uuid\Uuid;

class CommitteeElectionSecondRoundNotificationMessage extends Message
{
    public static function create(Adherent $adherent, Committee $committee, string $committeeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '[Désignations] Un deuxième tour va bientôt commencer',
            [
                'first_name' => $adherent->getFirstName(),
                'committee_name' => static::escape($committee->getName()),
                'committee_url' => $committeeUrl,
            ]
        );
    }
}
