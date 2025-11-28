<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

abstract class AbstractAnnouncementMessage extends AbstractRenaissanceMessage
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $first = array_shift($adherents);

        $message = new static(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            $election->getTitle(),
            [
                'vote_title' => $election->getTitle(),
                'vote_start_date' => static::formatDate($election->getVoteStartDate(), 'd MMMM y'),
                'vote_start_hour' => static::formatDate($election->getVoteStartDate(), 'HH\'h\'mm'),
                'vote_end_date' => static::formatDate($election->getVoteEndDate(), 'd MMMM y'),
                'vote_end_hour' => static::formatDate($election->getVoteEndDate(), 'HH\'h\'mm'),
                'year' => $election->getDesignation()->targetYear,
                'description' => nl2br($election->getDesignation()->getDescription() ?? ''),
                'primary_link' => $url,
            ],
            [
                'first_name' => $first->getFirstName(),
            ]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), [
                'first_name' => $adherent->getFirstName(),
            ]);
        }

        return $message;
    }
}
