<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NationalEventTransportMode extends Constraint
{
    public string $messageVisitDayMissing = 'Veillez sélectionner votre jour de visite.';
    public string $messageTransportMissing = 'Veillez sélectionner le forfait.';
    public string $messageInvalidTransport = 'Le mode de transport sélectionné n\'est pas disponible pour le jour de visite choisi.';
    public string $messageTransportLimit = 'Le quota de places pour ce mode de transport est atteint.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
