<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class CommitteeEvent extends Event implements GeocodableEntityEventInterface
{
    private $committee;

    public function __construct(Committee $committee = null)
    {
        $this->committee = $committee;
    }

    public function getCommittee()
    {
        return $this->committee;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->committee;
    }
}
