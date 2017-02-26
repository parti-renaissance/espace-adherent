<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CommitteeContactMembersMessage extends MailjetMessage
{
    /**
     * @param Adherent[] $recipients
     * @param Adherent   $host
     * @param string     $content
     *
     * @return CommitteeContactMembersMessage
     */
    public static function create(array $recipients, Adherent $host, string $content): self
    {
        $message = new self(
            Uuid::uuid4(),
            '63337',
            $host->getEmailAddress(),
            $host->getFullName(),
            "L'animateur d'un comité que vous suivez vous a envoyé un message",
            [
                'animator_firstname' => self::escape($host->getFirstName()),
                'target_message' => nl2br(self::escape($content)),
            ],
            [
                'target_firstname' => self::escape($host->getFirstName()),
            ],
            $host->getEmailAddress(),
            Uuid::uuid4()
        );

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
