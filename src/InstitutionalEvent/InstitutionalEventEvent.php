<?php

namespace AppBundle\InstitutionalEvent;

use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class InstitutionalEventEvent extends Event implements GeocodableEntityEventInterface
{
    protected $institutionalEvent;

    public function __construct(InstitutionalEvent $institutionalEvent)
    {
        $this->institutionalEvent = $institutionalEvent;
    }

    public function getInstitutionalEvent(): InstitutionalEvent
    {
        return $this->institutionalEvent;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->institutionalEvent;
    }
}
