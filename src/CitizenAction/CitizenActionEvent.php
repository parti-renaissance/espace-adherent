<?php

namespace AppBundle\CitizenAction;

use AppBundle\Entity\CitizenAction;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class CitizenActionEvent extends Event implements GeocodableEntityEventInterface
{
    private $action;

    public function __construct(CitizenAction $action)
    {
        $this->action = $action;
    }

    public function getCitizenAction(): CitizenAction
    {
        return $this->action;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->action;
    }
}
