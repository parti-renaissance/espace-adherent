<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionConfirmationMessage extends AbstractBesoinDEuropeMessage
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
