<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceMagicLinkMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $url): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre lien de connexion',
            [],
            ['magic_link' => $url],
        );
    }
}
