<?php

declare(strict_types=1);

namespace App\Geocoder\Event;

use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;

class DefaultEvent implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    public function __construct(private readonly GeocodableInterface $object)
    {
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->object;
    }
}
