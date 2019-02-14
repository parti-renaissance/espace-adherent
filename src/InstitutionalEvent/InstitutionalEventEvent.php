<?php

namespace AppBundle\InstitutionalEvent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class InstitutionalEventEvent extends Event implements GeocodableEntityEventInterface
{
    protected $author;
    protected $event;

    public function __construct(?Adherent $author, InstitutionalEvent $event)
    {
        $this->author = $author;
        $this->event = $event;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getEvent(): InstitutionalEvent
    {
        return $this->event;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->event;
    }
}
