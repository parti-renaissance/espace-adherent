<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectComment;
use Ramsey\Uuid\Uuid;

final class CitizenProjectCommentMessage extends Message
{
    public static function create(array $recipients, CitizenProjectComment $comment): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
        }

        $author = $comment->getAuthor();

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($author, $comment),
            [],
            $author->getEmailAddress()
        );

        $message->setSenderName($author->getFullName());

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

    private static function getTemplateVars(Adherent $author, CitizenProjectComment $comment): array
    {
        return [
            'citizen_project_host_first_name' => self::escape($author->getFirstName()),
            'citizen_project_host_message' => $comment->getContent(),
        ];
    }
}
