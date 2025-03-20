<?php

namespace App\Membership\Event;

use App\Entity\Adherent;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    public function __construct(
        private readonly Adherent $adherent,
        private readonly ?bool $allowEmailNotifications = null,
        private readonly ?bool $allowMobileNotifications = null,
        public readonly ?string $referrerPublicId = null,
        public readonly ?string $referralIdentifier = null,
    ) {
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function allowEmailNotifications(): ?bool
    {
        return $this->allowEmailNotifications;
    }

    public function allowMobileNotifications(): ?bool
    {
        return $this->allowMobileNotifications;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->adherent;
    }
}
