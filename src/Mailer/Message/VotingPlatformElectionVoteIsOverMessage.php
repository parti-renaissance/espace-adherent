<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

final class VotingPlatformElectionVoteIsOverMessage extends Message
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $first = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            sprintf('[%s] Les résultats sont disponibles', DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType() ? 'Élections internes' : 'Désignations'),
            [
                'election_type' => $election->getDesignationType(),
                'name' => static::escape($election->getElectionEntity()->getName()),
                'page_url' => $url,
            ],
            [
                'first_name' => $first->getFirstName(),
            ]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), [
                'first_name' => $adherent->getFirstName(),
            ]);
        }

        return $message;
    }
}
