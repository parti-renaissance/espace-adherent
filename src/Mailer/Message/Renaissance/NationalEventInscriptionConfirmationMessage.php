<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function create(EventInscription $eventInscription, string $editUrl): self
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
                'edit_url' => $editUrl,
            ]
        );
    }
}
