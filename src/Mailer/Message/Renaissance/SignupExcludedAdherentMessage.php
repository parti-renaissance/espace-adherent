<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use Symfony\Component\Uid\Uuid;

class SignupExcludedAdherentMessage extends AbstractRenaissanceMessage
{
    public static function create(string $email): self
    {
        return new self(
            Uuid::v4(),
            $email,
            null,
            'Réinscription impossible',
            [],
        );
    }
}
