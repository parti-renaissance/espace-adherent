<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use Ramsey\Uuid\Uuid;

final class VotingPlatformElectionSecondRoundNotificationMessage extends AbstractVotingPlatformMessage
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $first = array_shift($adherents);

        $daysLeft = $election->getDesignation()->getAdditionalRoundDuration();

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            \sprintf('[%s] Vous avez %d jours pour voter à nouveau.', self::getMailSubjectPrefix($election->getDesignation()), $daysLeft),
            [
                'election_type' => $election->getDesignationType(),
                'days_left' => $daysLeft,
                'second_round_end_date' => static::formatDate($election->getSecondRoundEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
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
