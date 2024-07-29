<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\VotingPlatform\Vote;
use Ramsey\Uuid\Uuid;

class VotingPlatformDefaultVoteConfirmationMessage extends AbstractRenaissanceVotingPlatformMessages
{
    public static function create(Vote $vote, string $voterKey): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $election = $vote->getElection();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            \sprintf('[%s] Félicitations, votre bulletin est dans l\'urne !', self::getMailSubjectPrefix($designation = $election->getDesignation())),
            [
                'election_type' => $designation->getDenomination(false, true).'s',
                'election_denomination' => $election->getDesignation()->getDenomination(false, true),
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
            ],
        );
    }
}
