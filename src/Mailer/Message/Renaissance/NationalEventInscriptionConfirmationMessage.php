<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionConfirmationMessage extends Message implements EuMessageInterface
{
    public static function create(EventInscription $eventInscription): self
    {
        return new self(Uuid::uuid4(), $eventInscription->addressEmail, $eventInscription->getFullName(), '');
    }
}
