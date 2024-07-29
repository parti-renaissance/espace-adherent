<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use Ramsey\Uuid\Uuid;

class VotingPlatformResultsReadyMessage extends AbstractRenaissanceVotingPlatformMessages
{
    public static function create(Election $election, array $adherents, string $url): self
    {
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            \sprintf('[%s] Les résultats sont disponibles !', self::getMailSubjectPrefix($election->getDesignation())),
            ['page_url' => $url]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }
}
