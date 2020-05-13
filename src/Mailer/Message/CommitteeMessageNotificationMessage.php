<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\CommitteeFeedItem;
use Ramsey\Uuid\Uuid;

final class CommitteeMessageNotificationMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[] $recipients
     */
    public static function create(array $recipients, CommitteeFeedItem $feedItem, string $subject): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            "[Comité local] $subject",
            static::getTemplateVars($feedItem->getAuthorFirstName(), $feedItem->getContent()),
            static::getRecipientVars($recipient->getFirstName()),
            $feedItem->getAuthor()->getEmailAddress()
        );

        $message->setSenderName($feedItem->getAuthor()->getFullName());

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $hostFirstName, string $hostMessage): array
    {
        return [
            'animator_firstname' => self::escape($hostFirstName),
            'target_message' => $hostMessage,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
