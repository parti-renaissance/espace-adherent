<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use Ramsey\Uuid\Uuid;

class CommitteeElectionVoteIsOpenMessage extends Message
{
    public static function create(
        Adherent $adherent,
        Committee $committee,
        Designation $designation,
        string $committeeUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '[Désignations] Désignez votre binôme d\'adhérents !',
            [
                'first_name' => $adherent->getFirstName(),
                'committee_name' => static::escape($committee->getName()),
                'vote_end_date' => static::formatDate($designation->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'committee_url' => $committeeUrl,
            ],
        );
    }
}
