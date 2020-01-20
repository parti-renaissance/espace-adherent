<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CitizenProjectContactActorsMessage extends Message
{
    /**
     * @param Adherent[] $recipients
     *
     * @return CitizenProjectContactActorsMessage
     */
    public static function create(array $recipients, Adherent $host, string $subject, string $content): self
    {
        $first = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            "[Projet citoyen] $subject",
            [
                'citizen_project_host_message' => $content,
                'citizen_project_host_firstname' => self::escape($host->getFirstName()),
            ],
            [],
            $host->getEmailAddress()
        );

        $message->setSenderName(sprintf('%s %s', $host->getFirstName(), $host->getLastName()));

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException('This message builder requires a collection of Adherent instances');
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName()
            );
        }

        return $message;
    }
}
