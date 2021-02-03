<?php

namespace App\CitizenAction;

use App\Entity\Adherent;
use App\Entity\Event\CitizenAction;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class CitizenActionEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

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
