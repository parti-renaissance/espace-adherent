<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\ValueObject\Genders;
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
        $first = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            '63337',
            $first->getEmailAddress(),
            self::fixMailjetParsing($first->getFullName()),
            'Des nouvelles de votre comité',
            [
                'animator_firstname' => self::escape($host->getFirstName()),
                'target_message' => $content,
            ],
            [
                'target_firstname' => self::escape($first->getFirstName()),
            ],
            $host->getEmailAddress()
        );

        $sender = $host->getFirstName().', ';
        $sender .= Genders::FEMALE === $host->getGender() ? 'animatrice' : 'animateur';
        $sender .= ' de votre comité';

        $message->setSenderName($sender);

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException('This message builder requires a collection of Adherent instances');
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                self::fixMailjetParsing($recipient->getFullName()),
                [
                    'target_firstname' => self::escape($recipient->getFirstName()),
                ]
            );
        }

        return $message;
    }
}
