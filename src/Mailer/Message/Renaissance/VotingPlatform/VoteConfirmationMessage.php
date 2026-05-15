<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Symfony\Component\Uid\Uuid;

class VoteConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function create(Election $election, Adherent $adherent, string $voterKey, string $url): self
    {
        return new self(
            Uuid::v4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Félicitations, votre bulletin est dans l\'urne',
            [
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
                'vote_title' => $election->getTitle(),
                'vote_end_date' => static::formatDate($election->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'primary_link' => $url,
            ],
        );
    }
}
