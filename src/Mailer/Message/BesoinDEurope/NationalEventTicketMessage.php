<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventTicketMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(EventInscription $eventInscription): self
    {
        $event = $eventInscription->event;

        return new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            $event->subjectTicketEmail,
            [
                'first_name' => $eventInscription->firstName,
                'last_name' => $eventInscription->lastName,
                'qr_code_img' => $eventInscription->ticketQRCodeFile,
                'header_ticket_image' => $event->imageTicketEmail,
                'event_mail_details' => $eventInscription->event->textTicketEmail,
            ],
        );
    }
}
