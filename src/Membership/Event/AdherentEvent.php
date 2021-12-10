<?php

namespace App\Membership\Event;

use App\Entity\Adherent;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class AdherentEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    private Adherent $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->adherent;
    }
}
