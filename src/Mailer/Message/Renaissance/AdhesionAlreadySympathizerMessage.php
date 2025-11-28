<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class AdhesionAlreadySympathizerMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $magicLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre adresse email',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'created_at' => static::formatDate($adherent->getRegisteredAt(), 'EEEE d MMMM y, HH\'h\'mm'),
                'magic_link' => $magicLink,
            ],
        );
    }
}
