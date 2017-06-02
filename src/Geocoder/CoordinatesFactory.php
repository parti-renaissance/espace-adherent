<?php

namespace AppBundle\Geocoder;

use AppBundle\Entity\Adherent;

class CoordinatesFactory
{
    public function createFromAdherent(Adherent $adherent): Coordinates
    {
        return new Coordinates($adherent->getLatitude(), $adherent->getLongitude());
    }
}
