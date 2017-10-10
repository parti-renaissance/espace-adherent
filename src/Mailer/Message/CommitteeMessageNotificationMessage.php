<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\ValueObject\Genders;
use Ramsey\Uuid\Uuid;

class CommitteeMessageNotificationMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[]        $recipients
     * @param CommitteeFeedItem $feedItem
     *
     * @return self
     */
    public static function create(array $recipients, CommitteeFeedItem $feedItem): self
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
            static::getTemplateVars($feedItem->getAuthorFirstName(), $feedItem->getContent()),
            static::getRecipientVars($recipient->getFirstName()),
            $feedItem->getAuthor()->getEmailAddress()
        );

        $sender = $feedItem->getAuthor()->getFirstName().', ';
        $sender .= Genders::FEMALE === $feedItem->getAuthor()->getGender() ? 'animatrice' : 'animateur';
        $sender .= ' de votre comité';

        $message->setSenderName($sender);

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
