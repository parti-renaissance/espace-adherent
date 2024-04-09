<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class BesoinDEuropeAccountAlreadyExistsMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(Adherent $adherent, string $magicLink, string $forgotPasswordLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'magic_link' => $magicLink,
                'forgot_password_link' => $forgotPasswordLink,
            ],
        );
    }
}
