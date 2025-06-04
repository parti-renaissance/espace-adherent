<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceMembershipAnniversaryMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $url): self
    {
        $lastYear = (int) (new \DateTime())->format('Y') - 1;

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre compte Renaissance a été créé',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'magic_link' => $url,
                'is_primo_n_1' => $adherent->isPrimoInYear($lastYear) ? 'true' : 'false',
            ],
        );
    }
}
