<?php

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Vote;
use Ramsey\Uuid\Uuid;

class CommitteeElectionVoteConfirmationMessage extends Message
{
    public static function create(Vote $vote, string $voterKey): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $election = $vote->getElection();
        $committee = $election->getElectionEntity()->getCommittee();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '[Désignations] Félicitations, vos bulletins sont dans l\'urne !',
            [
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
                'committee_name' => static::escape($committee->getName()),
                'vote_end_date' => static::formatDate($election->getRealVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            ],
        );
    }
}
