<?php

namespace AppBundle\CitizenAction;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class CitizenActionEvent extends Event implements GeocodableEntityEventInterface
{
    private $action;
    private $author;

    public function __construct(CitizenAction $action, Adherent $author = null)
    {
        $this->action = $action;
        $this->author = $author;
    }

    public function getCitizenAction(): CitizenAction
    {
        return $this->action;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->action;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }
}
