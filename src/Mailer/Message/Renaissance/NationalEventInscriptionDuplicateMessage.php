<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionDuplicateMessage extends AbstractRenaissanceMessage
{
    public static function create(EventInscription $eventInscription, string $myInscriptionUrl): self
    {
        $event = $eventInscription->event;

        return new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            'Votre inscription à l’événement '.$event->getName(),
            [
                'first_name' => $eventInscription->firstName,
                'primary_link' => $myInscriptionUrl,
            ],
        );
    }
}
