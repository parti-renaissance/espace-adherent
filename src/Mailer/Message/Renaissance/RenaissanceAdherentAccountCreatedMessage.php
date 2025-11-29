<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceAdherentAccountCreatedMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre compte Renaissance a été créé',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'activation_link' => $confirmationLink,
            ],
        );
    }
}
