<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventContactMembersMessage extends Message
{
    /**
     * @param EventRegistration[] $recipients
     *
     * @return EventContactMembersMessage
     */
    public static function create(array $recipients, Adherent $organizer, string $subject, string $content): self
    {
        $recipients = array_values($recipients);
        $recipient = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName(),
            "[Événement] $subject",
            [
                'organizer_firstname' => self::escape($organizer->getFirstName()),
                'target_message' => $content,
                'event_name' => $recipient->getEvent()->getName(),
                'sender_email' => $organizer->getEmailAddress(),
            ],
            ['target_firstname' => self::escape($recipient->getFirstName())],
            $organizer->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof EventRegistration) {
                throw new \InvalidArgumentException('This message builder requires a collection of EventRegistration instances');
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
