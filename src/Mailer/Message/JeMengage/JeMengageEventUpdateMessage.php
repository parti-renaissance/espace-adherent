<?php

namespace App\Mailer\Message\JeMengage;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class JeMengageEventUpdateMessage extends AbstractJeMengageMessage
{
    public static function create(array $recipients, Adherent $host, BaseEvent $event, string $icalEventUrl): Message
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
            'Un événement auquel vous participez a été mis à jour',
            static::getTemplateVars($event, $icalEventUrl),
            static::getRecipientVars($recipient),
            $host->getEmailAddress()
        ));

        /* @var EventRegistration[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName().' '.$recipient->getLastName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(BaseEvent $event, string $icalEventUrl): array
    {
        return [
            'event_name' => self::escape($event->getName()),
            'event_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
            'event_hour' => sprintf(
                '%sh%s',
                static::formatDate($event->getLocalBeginAt(), 'HH'),
                static::formatDate($event->getLocalBeginAt(), 'mm')
            ),
            'event_address' => $event->getInlineFormattedAddress(),
            'calendar_url' => $icalEventUrl,
        ];
    }

    private static function getRecipientVars(EventRegistration $recipient): array
    {
        return ['first_name' => self::escape($recipient->getFirstName())];
    }
}
