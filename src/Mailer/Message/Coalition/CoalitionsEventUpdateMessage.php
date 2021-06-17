<?php

namespace App\Mailer\Message\Coalition;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class CoalitionsEventUpdateMessage extends AbstractCoalitionMessage
{
    public static function create(array $recipients, BaseEvent $event, string $eventUrl): Message
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, \get_class($recipient)));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName(),
            '✊ Un événement a été modifié',
            static::getTemplateVars($event, $eventUrl),
            static::getRecipientVars($recipient)
        );

        /* @var EventRegistration[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName(),
                static::getRecipientVars($recipient)
            );
        }

        return self::updateSenderInfo($message);
    }

    private static function getTemplateVars(BaseEvent $event, string $eventUrl): array
    {
        return [
            'event_name' => self::escape($event->getName()),
            'event_url' => $eventUrl,
            'event_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
            'event_hour' => sprintf(
                '%sh%s',
                static::formatDate($event->getLocalBeginAt(), 'HH'),
                static::formatDate($event->getLocalBeginAt(), 'mm')
            ),
            'event_address' => $event->getInlineFormattedAddress(),
            'event_online' => $event->isOnline(),
            'event_visio_url' => $event->getVisioUrl(),
        ];
    }

    private static function getRecipientVars(EventRegistration $recipient): array
    {
        return ['first_name' => self::escape($recipient->getFirstName())];
    }
}
