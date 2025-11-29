<?php

declare(strict_types=1);

namespace App\Geocoder;

use App\Geocoder\Exception\GeocodingException;
use Geocoder\Geocoder as BazingaGeocoder;

class Geocoder
{
    private $geocoder;

    public function __construct(BazingaGeocoder $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    public function geocode(string $address): Coordinates
    {
        try {
            $addresses = $this->geocoder->geocode($address);
        } catch (\Exception $exception) {
            throw GeocodingException::create($address, $exception);
        }

        if (!\count($addresses)) {
            throw GeocodingException::create($address);
        }

        [$longitude, $latitude] = $addresses->first()->getCoordinates()->toArray();

        return new Coordinates($latitude, $longitude);
    }
}
