<?php

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Vote;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

final class VotingPlatformElectionVoteConfirmationMessage extends Message
{
    public static function create(Vote $vote, string $voterKey): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $election = $vote->getElection();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            sprintf('[%s] Félicitations, vos bulletins sont dans l\'urne !', DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType() ? 'Élections internes' : 'Désignations'),
            [
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
                'election_type' => $election->getDesignationType(),
                'vote_end_date' => static::formatDate($election->getRealVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            ],
        );
    }
}
