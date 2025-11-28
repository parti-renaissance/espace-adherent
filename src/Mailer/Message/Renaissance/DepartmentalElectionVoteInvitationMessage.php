<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class DepartmentalElectionVoteInvitationMessage extends AbstractRenaissanceMessage
{
    /** @param Adherent[] $adherents */
    public static function create(array $adherents): self
    {
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Convocation aux votes des adhérents Renaissance pour l’élection de leur bureau départemental'
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }
}
