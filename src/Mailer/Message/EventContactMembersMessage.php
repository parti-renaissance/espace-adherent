<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventContactMembersMessage extends Message
{
    /**
     * @param EventRegistration[] $recipients
     * @param Adherent            $organizer
     * @param string              $content
     *
     * @return EventContactMembersMessage
     */
    public static function create(array $recipients, Adherent $organizer, string $content): self
    {
        $recipients = array_values($recipients);
        $recipient = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName(),
            [
                'organizer_firstname' => self::escape($organizer->getFirstName()),
                'target_message' => $content,
            ],
            [
                'target_firstname' => self::escape($recipient->getFirstName()),
            ],
            $organizer->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof EventRegistration) {
                throw new \InvalidArgumentException(
                    'This message builder requires a collection of EventRegistration instances'
                );
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName(),
                [
                    'target_firstname' => self::escape($recipient->getFirstName()),
                ]
            );
        }

        return $message;
    }
}
