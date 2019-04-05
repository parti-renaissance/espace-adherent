<?php

namespace AppBundle\Donation;

use AppBundle\Geocoder\GeocodableEntityEventInterface;
use AppBundle\Geocoder\GeocodableInterface;

final class DonationWasCreatedEvent extends DonationEvent implements GeocodableEntityEventInterface
{
    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->getDonation();
    }
}
