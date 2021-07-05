<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use Ramsey\Uuid\Uuid;

final class VotingPlatformVoteReminderMessage extends Message
{
    public static function create(Election $election, Adherent $adherent, string $pageUrl): self
    {
        $designation = $election->getDesignation();
        $now = new \DateTime();
        $endDate = $designation->getVoteEndDate();

        $diff = $endDate->diff($now);

        if ($diff->days > 0) {
            $remainingTime = $diff->days.' jours';
        } else {
            $remainingTime = $diff->h.' heures';
        }

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            sprintf('[Rappel] Il vous reste %s pour participer !', $remainingTime),
            [
                'remaining_time' => $remainingTime,
                'first_name' => $adherent->getFirstName(),
                'election_type' => $election->getDesignationType(),
                'name' => $election->getElectionEntityName(),
                'vote_end_date' => static::formatDate($designation->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'page_url' => $pageUrl,
            ]
        );
    }
}
