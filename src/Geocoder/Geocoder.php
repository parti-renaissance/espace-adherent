<?php

namespace App\Geocoder;

use App\Geocoder\Exception\GeocodingException;
use Geocoder\Geocoder as BazingaGeocoder;

class Geocoder implements GeocoderInterface
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

        $geocoded = $addresses->first();

        return new Coordinates($geocoded->getLatitude(), $geocoded->getLongitude());
    }
}
