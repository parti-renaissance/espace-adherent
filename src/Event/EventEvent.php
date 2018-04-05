<?php

namespace AppBundle\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event as CommitteeEvent;
use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;
use Symfony\Component\EventDispatcher\Event;

class EventEvent extends Event implements GeocodableEntityEventInterface
{
    protected $author;
    protected $event;
    protected $committee;

    public function __construct(?Adherent $author, CommitteeEvent $event, Committee $committee = null)
    {
        $this->author = $author;
        $this->event = $event;
        $this->committee = $committee;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getEvent(): CommitteeEvent
    {
        return $this->event;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->event;
    }
}
