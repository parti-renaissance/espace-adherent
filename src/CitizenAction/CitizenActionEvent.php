<?php

namespace App\CitizenAction;

use App\Entity\Adherent;
use App\Entity\CitizenAction;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
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
