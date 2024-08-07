<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use Ramsey\Uuid\Uuid;

class VotingPlatformLocalElectionVoteIsOpenMessage extends AbstractRenaissanceVotingPlatformMessages
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $first = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            \sprintf('[%s] Le vote est ouvert !', self::getMailSubjectPrefix($election->getDesignation())),
            [
                'vote_end_date' => static::formatDate($election->getVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'name' => $election->getElectionEntityName(),
                'election_type' => $election->getDesignationType(),
                'election_denomination' => $election->getDesignation()->getDenomination(false, true),
                'page_url' => $url,
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
