<?php

declare(strict_types=1);

namespace App\Mailer\Message\JEM;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class JEMNationalEventTicketMessage extends AbstractJEMMessage
{
    public static function create(EventInscription $eventInscription, bool $useAppMobile): self
    {
        $event = $eventInscription->event;

        $message = new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            $event->subjectTicketEmail,
            [
                'uuid' => $eventInscription->getUuid()->toString(),
                'first_name' => $eventInscription->firstName,
                'last_name' => $eventInscription->lastName,
                'qr_code_img' => $eventInscription->isTicketReady() ? $eventInscription->ticketQRCodeFile : null,
                'header_ticket_image' => $event->imageTicketEmail,
                'event_mail_details' => $eventInscription->event->textTicketEmail,
                'ticket_custom_detail' => $eventInscription->ticketCustomDetail,
                'ticket_bracelet' => $eventInscription->ticketBracelet,
                'ticket_bracelet_color' => $eventInscription->ticketBraceletColor,
                'transport_info' => nl2br((string) $eventInscription->transportDetail),
                'accommodation_info' => nl2br((string) $eventInscription->accommodationDetail),
                'custom_info' => nl2br((string) $eventInscription->customDetail),
                'is_member' => $isMember = (null !== $eventInscription->adherent),
                'is_external' => !$isMember,
                'has_app_mobile' => $useAppMobile,
            ],
        );

        return static::updateSenderInfo($message);
    }
}
