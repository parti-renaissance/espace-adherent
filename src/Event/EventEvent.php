<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event as CommitteeEvent;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
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
