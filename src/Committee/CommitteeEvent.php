<?php

namespace App\Committee;

use App\Entity\Committee;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
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
