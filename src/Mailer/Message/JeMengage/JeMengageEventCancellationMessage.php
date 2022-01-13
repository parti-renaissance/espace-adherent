<?php

namespace App\Mailer\Message\JeMengage;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class JeMengageEventCancellationMessage extends AbstractJeMengageMessage
{
    public static function create(array $recipients, Adherent $host, BaseEvent $event, string $eventsLink): Message
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, \get_class($recipient)));
        }

        $message = self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName().' '.$recipient->getLastName(),
            sprintf('L\'événement "%s" a été annulé.', $event->getName()),
            ['event_name' => $event->getName(), 'events_url' => $eventsLink],
            self::getRecipientVars($recipient),
            $host->getEmailAddress()
        ));

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName().' '.$recipient->getLastName(),
                self::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    public static function getRecipientVars(EventRegistration $eventRegistration): array
    {
        return ['first_name' => self::escape($eventRegistration->getFirstName())];
    }
}
