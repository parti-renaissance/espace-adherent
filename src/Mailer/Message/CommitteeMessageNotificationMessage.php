<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use Ramsey\Uuid\Uuid;

class CommitteeMessageNotificationMessage extends Message
{
    public static function create(array $recipients, CommitteeFeedItem $feedItem, string $subject): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
        }

        $host = $feedItem->getAuthor();

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($feedItem, $host, $subject),
            static::getRecipientVars($recipient),
            $host->getEmailAddress()
        );

        $message->setSenderName($host->getFullName());

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
            'host_first_name' => self::escape($host->getFirstName()),
            'subject' => $subject,
            'message' => $feedItem->getContent(),
        ];
    }

    private static function getRecipientVars(Adherent $recipient): array
    {
        return [
            'first_name' => self::escape($recipient->getFirstName()),
        ];
    }
}
