<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\CitizenProject;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
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
