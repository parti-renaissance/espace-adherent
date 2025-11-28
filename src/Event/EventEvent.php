<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event as SfEvent;

class EventEvent extends SfEvent implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    protected $author;
    protected $event;

    public function __construct(?Adherent $author, Event $event)
    {
        $this->author = $author;
        $this->event = $event;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->event;
    }

    public function needSendMessage(): bool
    {
        return !$this->event->getCommittee();
    }
}
