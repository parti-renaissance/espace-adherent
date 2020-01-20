<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenActionContactParticipantsMessage extends Message
{
    /**
     * @param EventRegistration[] $recipients
     */
    public static function create(array $recipients, Adherent $organizer, string $subject, string $content): self
    {
        $recipient = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            "[Action citoyenne] $subject",
            [
                'citizen_project_host_message' => $content,
                'citizen_project_host_firstname' => self::escape($organizer->getFirstName()),
            ],
            [],
            $organizer->getEmailAddress()
        );

        $message->setSenderName(sprintf('%s %s', $organizer->getFirstName(), $organizer->getLastName()));

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof EventRegistration) {
                throw new \InvalidArgumentException('This message builder requires a collection of EventRegistration instances');
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName()
            );
        }

        return $message;
    }
}
