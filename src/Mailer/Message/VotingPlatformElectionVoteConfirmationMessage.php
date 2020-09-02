<?php

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Vote;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

class VotingPlatformElectionVoteConfirmationMessage extends Message
{
    public static function create(Vote $vote, string $voterKey): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $election = $vote->getElection();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '[Désignations] A voté !',
            [
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
                'name' => static::escape($election->getElectionEntity()->getName()),
                'is_copol' => DesignationTypeEnum::COPOL === $election->getDesignationType(),
                'vote_end_date' => static::formatDate($election->getRealVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            ],
        );
    }
}
