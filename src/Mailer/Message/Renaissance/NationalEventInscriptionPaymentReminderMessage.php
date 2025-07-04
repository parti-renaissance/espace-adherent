<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\NationalEvent\EventInscription;
use Ramsey\Uuid\Uuid;

class NationalEventInscriptionPaymentReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(EventInscription $eventInscription, string $finalizeUrl): self
    {
        $event = $eventInscription->event;

        $selectedTransportConfig = null;
        foreach ($event->transportConfiguration['transports'] ?? [] as $transport) {
            if ($transport['id'] === $eventInscription->transport) {
                $selectedTransportConfig = $transport;
                break;
            }
        }

        $selectedAccommodationConfig = null;
        foreach ($event->transportConfiguration['hebergements'] ?? [] as $accommodation) {
            if ($accommodation['id'] === $eventInscription->accommodation) {
                $selectedAccommodationConfig = $accommodation;
                break;
            }
        }

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
                'transport_title' => $selectedTransportConfig['titre'] ?? null,
                'accommodation_title' => $selectedAccommodationConfig['titre'] ?? null,
                'cancellation_date' => (clone $eventInscription->getCreatedAt())->modify(\sprintf('+%d hours', EventInscription::CANCELLATION_DELAY_IN_HOUR + 20))->format('d/m/Y à H:i'),
            ],
        );
    }
}
