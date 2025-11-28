<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

abstract class AbstractVoteReminderMessage extends AbstractRenaissanceMessage
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $adherent = array_shift($adherents);

        $message = new static(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::generateSubject(),
            [
                'vote_title' => $election->getTitle(),
                'vote_end_hour' => self::formatDate($election->getVoteEndDate(), 'HH\'h\'mm'),
                'primary_link' => $url,
            ],
            ['first_name' => $adherent->getFirstName()]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), [
                'first_name' => $adherent->getFirstName(),
            ]);
        }

        return $message;
    }

    abstract protected static function generateSubject(): string;
}
