<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class AdherentMembershipReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $adhesionUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Terminez votre adhésion',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'primary_link' => $adhesionUrl,
            ]
        );
    }
}
