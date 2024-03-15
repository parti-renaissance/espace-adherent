<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionConfirmationMessage extends Message implements EuMessageInterface
{
    public static function create(EventInscription $eventInscription): self
    {
        $event = $eventInscription->event;

        return new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            'Votre inscription - '.$event->getName(),
            [
                'event_name' => $event->getName(),
                'text_confirmation' => $event->textConfirmation,
            ]
        );
    }
}
