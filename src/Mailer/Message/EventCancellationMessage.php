<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventCancellationMessage extends Message
{
    public static function create(
        array $recipients,
        Adherent $host,
        BaseEvent $event,
        string $eventsLink
    ): self {
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
            static::getTemplateVars($event, $eventsLink),
            static::getRecipientVars($recipient),
            $host->getEmailAddress()
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

    private static function getTemplateVars(BaseEvent $event, string $eventsLink): array
    {
        return [
            'event_name' => $event->getName(),
            'event_url' => $eventsLink,
        ];
    }

    private static function getRecipientVars(EventRegistration $registration): array
    {
        return [
            'first_name' => self::escape($registration->getFirstName()),
        ];
    }
}
