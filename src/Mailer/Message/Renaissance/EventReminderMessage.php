<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Event\EventRegistration;
use Ramsey\Uuid\Uuid;

class EventReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(EventRegistration $eventRegistration, string $eventLink): self
    {
        $event = $eventRegistration->getEvent();

        return new self(
            Uuid::uuid4(),
            $eventRegistration->getEmailAddress(),
            $eventRegistration->getFullName(),
            '[Rappel] Participation à un événement',
            [
                'target_firstname' => $eventRegistration->getFirstName(),
                'event_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
                'event_hour' => static::formatDate($event->getLocalBeginAt(), 'HH\'h\'mm'),
                'event_name' => $event->getName(),
                'event_address' => self::escape($event->getInlineFormattedAddress()),
                'event_description' => $event->getDescription(),
                'animator_firstname' => $event->getAuthor()?->getFirstName(),
                'event_slug' => $eventLink,
                'visio_url' => $event->getVisioUrl(),
                'live_url' => $event->liveUrl,
            ],
        );
    }
}
