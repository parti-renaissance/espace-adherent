<?php

declare(strict_types=1);

namespace App\Committee\Event;

use App\Entity\Committee;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractCommitteeEvent extends Event implements CommitteeEventInterface, GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    public function __construct(private readonly Committee $committee)
    {
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->committee;
    }
}
