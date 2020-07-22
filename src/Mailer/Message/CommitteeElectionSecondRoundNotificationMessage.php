<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use Ramsey\Uuid\Uuid;

class CommitteeElectionSecondRoundNotificationMessage extends Message
{
    public static function create(
        Adherent $adherent,
        Election $election,
        Committee $committee,
        string $committeeUrl
    ): self {
        $daysLeft = (int) $election->getDesignation()->getAdditionalRoundDuration();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            "[Désignations] Égalité des candidats dans votre comité : vous avez $daysLeft jours pour revoter",
            [
                'first_name' => $adherent->getFirstName(),
                'committee_name' => static::escape($committee->getName()),
                'days_left' => $daysLeft,
                'second_round_end_date' => static::formatDate($election->getSecondRoundEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'committee_url' => $committeeUrl,
            ]
        );
    }
}
