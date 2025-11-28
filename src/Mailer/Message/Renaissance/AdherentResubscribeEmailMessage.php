<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResubscribeEmailMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $url): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Réabonnez-vous aux communications de Renaissance',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'page_url' => $url,
            ]
        );
    }
}
