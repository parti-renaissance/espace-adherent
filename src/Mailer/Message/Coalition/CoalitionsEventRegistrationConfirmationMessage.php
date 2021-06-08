<?php

namespace App\Mailer\Message\Coalition;

use App\Entity\Event\CauseEvent;
use App\Entity\Event\EventRegistration;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class CoalitionsEventRegistrationConfirmationMessage extends AbstractCoalitionMessage
{
    public static function create(EventRegistration $eventRegistration, string $eventUrl): Message
    {
        $event = $eventRegistration->getEvent();
        $organiser = $event instanceof CauseEvent ? $event->getCause() : $event->getCoalition();

        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $eventRegistration->getEmailAddress(),
            $eventRegistration->getFirstName(),
            '✊ Inscription confirmée',
            [
                'first_name' => self::escape($eventRegistration->getFirstName()),
                'event_name' => self::escape($event->getName()),
                'event_link' => $eventUrl,
                'event_organiser' => $organiser->getName(),
            ]
        ));
    }
}
