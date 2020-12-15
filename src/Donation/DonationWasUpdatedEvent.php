<?php

namespace App\Donation;

use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;

final class DonationWasUpdatedEvent extends DonationEvent implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->getDonation();
    }
}
