<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionPaymentReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(EventInscription $eventInscription, string $finalizeUrl): self
    {
        $event = $eventInscription->event;

        return new self(
            Uuid::uuid4(),
            $eventInscription->addressEmail,
            $eventInscription->getFullName(),
            'Votre inscription à l’événement '.$event->getName().' - Rappel de paiement',
            [
                'first_name' => $eventInscription->firstName,
                'last_name' => $eventInscription->lastName,
                'primary_link' => $finalizeUrl,
                'amount' => $eventInscription->amount / 100,
                'transport_title' => $eventInscription->getTransportConfig()['titre'] ?? null,
                'visit_day' => $eventInscription->getVisitDayConfig()['titre'] ?? null,
                'place_1' => $eventInscription->packageValues['partie1place'] ?? null,
                'place_2' => $eventInscription->packageValues['partie2place'] ?? null,
                'accommodation_title' => $eventInscription->getAccommodationConfig()['titre'] ?? null,
                'cancellation_date' => (clone $eventInscription->getCreatedAt())->modify(\sprintf('+%d minutes', EventInscription::CANCELLATION_DELAY_IN_MIN))->format('d/m/Y à H:i'),
            ],
        );
    }
}
