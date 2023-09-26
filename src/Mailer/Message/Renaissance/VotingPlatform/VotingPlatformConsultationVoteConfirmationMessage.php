<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\VotingPlatform\Vote;
use Ramsey\Uuid\Uuid;

class VotingPlatformConsultationVoteConfirmationMessage extends AbstractRenaissanceVotingPlatformMessages
{
    public static function create(Vote $vote): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $designation = $vote->getElection()->getDesignation();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Merci pour votre participation !',
            [
                'consultation_name' => $designation->getTitle(),
                'first_name' => $adherent->getFirstName(),
            ],
        );
    }
}
