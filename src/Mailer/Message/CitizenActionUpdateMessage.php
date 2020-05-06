<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\CitizenAction;
use App\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenActionUpdateMessage extends Message
{
    public static function create(
        array $recipients,
        Adherent $host,
        CitizenAction $event,
        string $eventUrl,
        string $icalEventUrl
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, \get_class($recipient)));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFirstName().' '.$recipient->getLastName(),
            'Une action citoyenne à laquelle vous participez a été mise à jour',
            static::getTemplateVars($event, $eventUrl, $icalEventUrl),
            static::getRecipientVars($recipient),
            $host->getEmailAddress()
        );

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

    private static function getTemplateVars(CitizenAction $event, string $eventUrl, string $icalEventUrl): array
    {
        return [
            'citizen_action_name' => self::escape($event->getName()),
            'citizen_action_url' => $eventUrl,
            'citizen_action_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
            'citizen_action_hour' => sprintf(
                '%sh%s',
                static::formatDate($event->getLocalBeginAt(), 'HH'),
                static::formatDate($event->getLocalBeginAt(), 'mm')
            ),
            'citizen_action_address' => $event->getInlineFormattedAddress(),
            'calendar_url' => $icalEventUrl,
        ];
    }

    private static function getRecipientVars(EventRegistration $recipient): array
    {
        return ['first_name' => self::escape($recipient->getFirstName())];
    }
}
