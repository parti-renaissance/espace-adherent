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
    public static function create(array $recipients, Adherent $organizer, string $eventName, string $subject, string $content): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', EventRegistration::class));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($organizer, $eventName, $subject, $content),
            static::getRecipientVars($recipient),
            $organizer->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof EventRegistration) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', EventRegistration::class));
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(Adherent $organizer, string $eventName, string $subject, string $content): array
    {
        return [
            'organizer_first_name' => static::escape($organizer->getFirstName()),
            'event_name' => static::escape($eventName),
            'subject' => static::escape($subject),
            'message' => static::escape($content),
        ];
    }

    private static function getRecipientVars(EventRegistration $registration): array
    {
        return [
            'recipient_first_name' => self::escape($registration->getFirstName()),
        ];
    }
}
