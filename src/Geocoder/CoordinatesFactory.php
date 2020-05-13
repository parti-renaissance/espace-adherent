<?php

namespace App\Geocoder;

use App\Entity\Adherent;
use App\Entity\PostAddress;

class CoordinatesFactory
{
    public function createFromAdherent(Adherent $adherent): Coordinates
    {
        return new Coordinates($adherent->getLatitude(), $adherent->getLongitude());
    }

    public function createFromPostAddress(PostAddress $postAddress): ?Coordinates
    {
        if (!$postAddress->hasCoordinates()) {
            return null;
        }

        return new Coordinates($postAddress->getLatitude(), $postAddress->getLongitude());
    }
}
