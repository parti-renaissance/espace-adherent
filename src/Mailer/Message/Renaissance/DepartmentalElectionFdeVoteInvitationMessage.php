<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class DepartmentalElectionFdeVoteInvitationMessage extends AbstractRenaissanceMessage
{
    /** @param Adherent[] $adherents */
    public static function create(array $adherents): self
    {
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Convocation au vote des adhérents Renaissance établis hors de France pour l’élection du bureau de l’Assemblée des Français de l’étranger'
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }
}
