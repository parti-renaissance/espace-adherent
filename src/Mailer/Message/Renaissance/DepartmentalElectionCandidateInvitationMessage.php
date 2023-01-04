<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class DepartmentalElectionCandidateInvitationMessage extends AbstractRenaissanceMessage
{
    /** @param Adherent[] $adherents */
    public static function create(array $adherents): self
    {
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Appel à candidature pour l\'élection des bureaux départementaux'
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }
}
