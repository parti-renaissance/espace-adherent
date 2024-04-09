<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventTicketMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(EventInscription $eventInscription, string $qrCodeData): self
    {
        return new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            '',
            [
                'first_name' => $eventInscription->firstName,
                'last_name' => $eventInscription->lastName,
                'qr_code_img' => $qrCodeData,
                'event_mail_details' => $eventInscription->event->textTicketEmail,
            ],
        );
    }
}
