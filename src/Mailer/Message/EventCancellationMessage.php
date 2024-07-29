<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventCancellationMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param EventRegistration[] $recipients
     */
    public static function create(array $recipients, Adherent $host, BaseEvent $event, string $eventsLink): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(\sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, $recipient::class));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName().' '.$recipient->getLastName(),
            \sprintf('L\'événement "%s" a été annulé.', $event->getName()),
            static::getTemplateVars($event->getName(), $eventsLink),
            self::getRecipientVars($recipient),
            $host->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName().' '.$recipient->getLastName(),
                self::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $eventName, string $eventsLink): array
    {
        return [
            'event_name' => $eventName,
            'event_slug' => $eventsLink,
        ];
    }

    private static function getRecipientVars(EventRegistration $eventRegistration): array
    {
        return [
            'target_firstname' => self::escape($eventRegistration->getFirstName()),
        ];
    }
}
