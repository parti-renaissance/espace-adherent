<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use Ramsey\Uuid\Uuid;

class CommitteeMessageNotificationMessage extends Message
{
    public static function create(CommitteeFeedItem $feedItem, array $recipients, string $subject): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
        }

        $author = $feedItem->getAuthor();

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($feedItem, $author, $subject),
            static::getRecipientVars($recipient),
            $author->getEmailAddress()
        );

        $message->setSenderName($author->getFullName());

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(CommitteeFeedItem $feedItem, Adherent $host, string $subject): array
    {
        return [
            'animator_firstname' => self::escape($host->getFirstName()),
            'target_subject' => $subject,
            'target_message' => $feedItem->getContent(),
        ];
    }

    private static function getRecipientVars(Adherent $adherent): array
    {
        return [
            'target_firstname' => self::escape($adherent->getFirstName()),
        ];
    }
}
