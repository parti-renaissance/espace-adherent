<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NationalEventTransportMode extends Constraint
{
    public string $messageAccommodationMissing = 'Veillez sélectionner une option.';
    public string $messageInvalidTransport = 'Le mode de transport sélectionné n\'est pas disponible pour le jour de visite choisi.';
    public string $messageInvalidAccommodation = 'L\'hébergement sélectionné n\'est pas disponible pour le jour de visite choisi.';
    public string $messageTransportLimit = 'Le quota de places pour ce mode de transport est atteint.';
    public string $messageAccommodationLimit = 'Le quota de places pour cet hébergement est atteint.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
