<?php

namespace AppBundle\Mailer\Message;

use AppBundle\BoardMember\BoardMemberMessage as BoardMemberMessageModel;
use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class BoardMemberMessage extends Message
{
    public static function create(BoardMemberMessageModel $model, array $recipients): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
        }

        $boardMember = $model->getFrom();

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($boardMember, $model),
            [],
            $boardMember->getEmailAddress()
        );

        $message->setSenderEmail('jemarche@en-marche.fr');
        $message->setSenderName($boardMember->getFullName());

        $message->addRecipient('jemarche@en-marche.fr', 'Je Marche');

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName()
            );
        }

        return $message;
    }

    private static function getTemplateVars(Adherent $boardMember, BoardMemberMessageModel $message): array
    {
        return [
            'member_first_name' => self::escape($boardMember->getFirstName()),
            'member_last_name' => self::escape($boardMember->getLastName()),
            'subject' => $message->getSubject(),
            'message' => $message->getContent(),
        ];
    }
}
