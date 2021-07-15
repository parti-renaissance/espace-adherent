<?php

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Vote;
use Ramsey\Uuid\Uuid;

final class VotingPlatformElectionVoteConfirmationMessage extends AbstractVotingPlatformMessage
{
    public static function create(Vote $vote, string $voterKey): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $election = $vote->getElection();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            sprintf('[%s] Félicitations, votre bulletin est dans l\'urne !', self::getMailSubjectPrefix($designation = $election->getDesignation())),
            [
                'election_type' => $designation->getDenomination(false, true).'s',
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
                'vote_end_date' => static::formatDate($election->getRealVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            ],
        );
    }
}
