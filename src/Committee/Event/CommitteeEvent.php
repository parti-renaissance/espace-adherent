<?php

namespace App\Committee\Event;

use App\Entity\Committee;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class CommitteeEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    private $committee;

    public function __construct(?Committee $committee = null)
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
