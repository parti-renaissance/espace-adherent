<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResubscribeEmailMessage extends Message
{
    public static function create(Adherent $adherent, string $url): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Réabonnez-vous aux communications de LaREM',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'page_url' => $url,
            ]
        );
    }
}
