<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class AdherentEvent extends Event implements GeocodableEntityEventInterface
{
    private $adherent;

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
