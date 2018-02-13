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
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, get_class($recipient)));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName(),
            self::getTemplateVars($organizer->getFirstName(), $content),
            self::getRecipientVars($recipient->getFirstName()),
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
                self::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $organizerFirstName, string $content): array
    {
        return [
            'organizer_firstname' => self::escape($organizerFirstName),
            'target_message' => $content,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
