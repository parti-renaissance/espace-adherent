<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function create(EventInscription $eventInscription): self
    {
        $message = new self(Uuid::uuid4(), $eventInscription->addressEmail, $eventInscription->getFullName(), '');

        $message->setSenderEmail('BDE@part-renaissance.fr');
        $message->setSenderName('BDE');

        return $message;
    }
}
