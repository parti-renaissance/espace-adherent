<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use Ramsey\Uuid\Uuid;

class CommitteeFeedNotificationMessage extends MailjetMessage
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[]        $recipients
     * @param CommitteeFeedItem $message
     *
     * @return self
     */
    public static function create(array $recipients, CommitteeFeedItem $message): self
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
            '54909',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Un animateur vous a envoyé un message',
            static::getTemplateVars($message->getAuthorFirstName(), $message->getContent()),
            static::getRecipientVars($recipient->getFirstName())
        );

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
            'animator_firstname' => $hostFirstName,
            'animator_message' => $hostMessage,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => $firstName,
        ];
    }
}
