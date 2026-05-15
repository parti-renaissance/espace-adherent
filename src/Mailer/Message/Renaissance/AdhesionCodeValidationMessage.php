<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Symfony\Component\Uid\Uuid;

class AdhesionCodeValidationMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $code, string $magicLink): self
    {
        return new self(
            Uuid::v4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre adresse email',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'code' => $code,
                'magic_link' => $magicLink,
            ],
        );
    }
}
