<?php

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReferentNews extends Constraint
{
    public string $zoneNotNull = 'Une zone est nécessaire pour une actualité référente';
    public string $invalidZoneType = 'Cette zone ne correspond pas à une région, un département ou un arrondissement';
    public string $invalidManagedZone = 'Oups, vous n\'avez pas accès à cette zone !';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
