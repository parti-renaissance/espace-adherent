<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventContactMembersMessage extends MailjetMessage
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
        $recipient = $recipients[0];

        $message = new self(
            Uuid::uuid4(),
            '116586',
            $recipient->getEmailAddress(),
            $recipient->getFirstName(),
            "L'organisateur d'un événement auquel vous êtes inscrit vous a envoyé un message",
            [
                'organizer_firstname' => self::escape($organizer->getFirstName()),
                'target_message' => $content,
            ],
            [
                'target_firstname' => self::escape($recipient->getFirstName()),
            ],
            $organizer->getEmailAddress(),
            Uuid::uuid4()
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
