<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use Ramsey\Uuid\Uuid;

final class VotingPlatformVoteStatusesIsOverMessage extends AbstractVotingPlatformMessage
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
            'Modification des statuts de LaREM',
            [
                'result_start_date' => static::formatDate($election->getDesignation()->getResultStartDate(), 'EEEE d MMMM y à HH\'h\'mm'),
                'page_url' => $url,
            ]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }
}
