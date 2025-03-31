<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(EventInscription $eventInscription): self
    {
        $event = $eventInscription->event;

        return new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            '[Rappel] Participation à un événement',
            [
                'event_name' => $event->getName(),
                'first_name' => $eventInscription->firstName,
                'last_name' => $eventInscription->lastName,
            ],
        );
    }
}
