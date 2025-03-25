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
                'event_hour' => \sprintf(
                    '%sh%s',
                    static::formatDate($event->getLocalBeginAt(), 'HH'),
                    static::formatDate($event->getLocalBeginAt(), 'mm')
                ),
                'event_name' => $event->getName(),
                'event_address' => $event->getInlineFormattedAddress(),
                'event_description' => $event->getDescription(),
                'animator_firstname' => $event->getAuthor()?->getFirstname(),
                'event_slug' => $eventLink,
            ],
        );
    }
}
