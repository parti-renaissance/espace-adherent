<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceLoginLinkMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $url): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre lien de connexion Renaissance',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'url' => $url,
            ],
        );
    }
}
