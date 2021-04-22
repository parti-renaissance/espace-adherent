<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class EventEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    protected $author;
    protected $event;

    public function __construct(?Adherent $author, BaseEvent $event)
    {
        $this->author = $author;
        $this->event = $event;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getEvent(): BaseEvent
    {
        return $this->event;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->event;
    }
}
