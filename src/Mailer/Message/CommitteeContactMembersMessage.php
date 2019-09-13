<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CommitteeContactMembersMessage extends Message
{
    /**
     * @param Adherent[] $recipients
     *
     * @return CommitteeContactMembersMessage
     */
    public static function create(array $recipients, Adherent $host, string $subject, string $content): self
    {
        $first = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            "[Comité local] $subject",
            [
                'animator_firstname' => self::escape($host->getFirstName()),
                'target_message' => $content,
            ],
            [
                'target_firstname' => self::escape($first->getFirstName()),
            ],
            $host->getEmailAddress()
        );

        $message->setSenderName($host->getFullName());

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException('This message builder requires a collection of Adherent instances');
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                [
                    'target_firstname' => self::escape($recipient->getFirstName()),
                ]
            );
        }

        return $message;
    }
}
