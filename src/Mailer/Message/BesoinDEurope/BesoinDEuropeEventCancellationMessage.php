<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use Ramsey\Uuid\Uuid;

final class BesoinDEuropeEventCancellationMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(array $recipients, Adherent $host, BaseEvent $event, string $eventsLink): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName().' '.$recipient->getLastName(),
            '',
            static::getTemplateVars($event->getName(), $eventsLink),
            self::getRecipientVars($recipient)
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
            'events_link' => $eventsLink,
        ];
    }

    private static function getRecipientVars(EventRegistration $eventRegistration): array
    {
        return [
            'target_firstname' => self::escape($eventRegistration->getFirstName()),
        ];
    }
}
