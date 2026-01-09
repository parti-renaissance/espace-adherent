<?php

declare(strict_types=1);

namespace App\Mailer\Message\JEM;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class JEMNationalEventInscriptionDuplicateMessage extends AbstractJEMMessage
{
    public static function create(EventInscription $eventInscription, string $myInscriptionUrl): self
    {
        $event = $eventInscription->event;

        $message = new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            'Votre inscription à l’événement '.$event->getName(),
            [
                'first_name' => $eventInscription->firstName,
                'primary_link' => $myInscriptionUrl,
            ],
        );

        return static::updateSenderInfo($message);
    }
}
