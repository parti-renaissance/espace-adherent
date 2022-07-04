<?php

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Vote;
use Ramsey\Uuid\Uuid;

final class VotingPlatformVoteStatusesVoteConfirmationMessage extends AbstractVotingPlatformMessage
{
    public static function create(Vote $vote, string $voterKey): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $election = $vote->getElection();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Félicitations, votre bulletin est dans l\'urne !',
            [
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
                'result_start_date' => static::formatDate($election->getDesignation()->getResultStartDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            ],
        );
    }
}
