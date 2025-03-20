<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function create(EventInscription $eventInscription, string $editUrl, ?string $civility, ?string $region, ?string $department): self
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
                'civility' => $civility,
                'first_name' => self::escape((string) $eventInscription->firstName),
                'last_name' => self::escape((string) $eventInscription->lastName),
                'email' => $eventInscription->addressEmail,
                'phone' => PhoneNumberUtils::format($eventInscription->phone),
                'region' => self::escape((string) $region),
                'department' => self::escape((string) $department),
                'birthdate' => self::escape($eventInscription->birthdate->format('d/m/Y')),
                'birth_place' => self::escape((string) $eventInscription->birthPlace),
                'inscription_uuid' => $eventInscription->getUuid()->toString(),
            ]
        );
    }
}
