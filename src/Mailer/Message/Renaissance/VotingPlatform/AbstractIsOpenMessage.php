<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use App\Utils\StringCleaner;
use Ramsey\Uuid\Uuid;

abstract class AbstractIsOpenMessage extends AbstractRenaissanceMessage
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $first = array_shift($adherents);
        $designation = $election->getDesignation();
        $description = $designation->getDescription() ?? $designation->wordingWelcomePage?->getContent();

        $message = new static(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            static::generateSubject(),
            [
                'vote_title' => $election->getTitle(),
                'vote_end_date' => static::formatDate($election->getVoteEndDate(), 'd MMMM y'),
                'vote_end_hour' => static::formatDate($election->getVoteEndDate(), 'HH\'h\'mm'),
                'description' => StringCleaner::removeMarkdown(nl2br($description ?? '')),
                'primary_link' => $url,
            ],
            ['first_name' => $first->getFirstName()]
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
