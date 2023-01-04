<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class DepartmentalElectionComplementaryCandidatureInformationMessage extends AbstractRenaissanceMessage
{
    /** @param Adherent[] $adherents */
    public static function create(array $adherents): self
    {
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Complément d\'information - Appel à candidatures des bureaux départementaux'
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }
}
