<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Symfony\Component\Uid\Uuid;

class SignupConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $magicLink): self
    {
        return new self(
            Uuid::v4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre inscription',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'magic_link' => $magicLink,
            ],
        );
    }
}
