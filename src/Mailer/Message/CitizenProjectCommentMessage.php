<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectComment;
use Ramsey\Uuid\Uuid;

class CitizenProjectCommentMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[]            $recipients
     * @param CitizenProjectComment $comment
     *
     * @return self
     */
    public static function create(array $recipients, CitizenProjectComment $comment): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $author = $comment->getAuthor()->getFirstName();
        $message = new self(
            Uuid::uuid4(),
            '275088',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Message de votre porteur de projet',
            [
                'citizen_project_host_firstname' => self::escape($author),
                'citizen_project_host_message' => $comment->getContent(),
            ],
            [],
            $comment->getAuthor()->getEmailAddress()
        );

        $sender = sprintf('%s, porteur de votre projet citoyen', $author);

        $message->setSenderName($sender);

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName()
            );
        }

        return $message;
    }
}
