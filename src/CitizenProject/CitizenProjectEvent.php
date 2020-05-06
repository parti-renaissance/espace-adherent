<?php

namespace App\CitizenProject;

use App\Entity\CitizenProject;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class CitizenProjectEvent extends Event implements GeocodableEntityEventInterface
{
    private $citizenProject;

    public function __construct(CitizenProject $citizenProject = null)
    {
        $this->citizenProject = $citizenProject;
    }

    public function getCitizenProject()
    {
        return $this->citizenProject;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->citizenProject;
    }
}
