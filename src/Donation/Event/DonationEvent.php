<?php

declare(strict_types=1);

namespace App\Donation\Event;

use App\Entity\Donation;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class DonationEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    private $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function getDonation(): Donation
    {
        return $this->donation;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->getDonation();
    }
}
