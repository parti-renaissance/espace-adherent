<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractCitizenInitiativeEvent extends Event implements GeocodableEntityEventInterface
{
    private $author;
    private $initiative;

    public function __construct(Adherent $author, CitizenInitiative $initiative)
    {
        $this->author = $author;
        $this->initiative = $initiative;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getCitizenInitiative(): CitizenInitiative
    {
        return $this->initiative;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->initiative;
    }
}
