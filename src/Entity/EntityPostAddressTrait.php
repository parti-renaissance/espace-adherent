<?php

namespace App\Entity;

use App\Geocoder\Coordinates;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityPostAddressTrait
{
    /**
     * @var PostAddress
     */
    #[Assert\NotBlank(groups: ['procuration:write'])]
    #[Assert\Valid(groups: ['contact_update', 'procuration:write'])]
    #[Groups(['contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write', 'profile_update'])]
    #[ORM\Embedded(class: PostAddress::class, columnPrefix: 'address_')]
    protected $postAddress;

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    public function setPostAddress(PostAddress $postAddress): void
    {
        $this->postAddress = $postAddress;
    }

    public function getInlineFormattedAddress($locale = 'fr_FR'): string
    {
        return $this->postAddress ? $this->postAddress->getInlineFormattedAddress($locale) : '';
    }

    #[Groups(['user_profile'])]
    public function getCountry(): ?string
    {
        return $this->postAddress->getCountry();
    }

    public function getCountryName(): ?string
    {
        return $this->postAddress->getCountry() ? Countries::getName($this->postAddress->getCountry()) : null;
    }

    public function getAddress(): ?string
    {
        return $this->postAddress->getAddress();
    }

    public function getAdditionalAddress(): ?string
    {
        return $this->postAddress->getAdditionalAddress();
    }

    #[Groups(['user_profile', 'export', 'adherent_autocomplete', 'national_event_inscription:webhook'])]
    public function getPostalCode(): ?string
    {
        return $this->postAddress->getPostalCode();
    }

    public function getCityName(): ?string
    {
        return $this->postAddress->getCityName();
    }

    public function getCity()
    {
        return $this->postAddress->getCity();
    }

    public function getInseeCode(): ?string
    {
        return $this->postAddress->getInseeCode();
    }

    public function getLatitude(): ?float
    {
        return $this->postAddress->getLatitude();
    }

    public function getLongitude(): ?float
    {
        return $this->postAddress->getLongitude();
    }

    public function isGeocoded(): bool
    {
        return $this->getLatitude() && $this->getLongitude();
    }

    public function getGeocodableAddress(): string
    {
        return $this->postAddress->getGeocodableAddress();
    }

    public function updateCoordinates(Coordinates $coordinates): void
    {
        $this->postAddress->updateCoordinates($coordinates);
    }

    public function resetCoordinates(): void
    {
        $this->postAddress->resetCoordinates();
    }

    public function getGeolocalisation(): array
    {
        return [
            'lng' => $this->getLongitude(),
            'lat' => $this->getLatitude(),
        ];
    }

    public function getGeocodableHash(): ?string
    {
        return $this->postAddress->getGeocodableHash();
    }

    public function setGeocodableHash(string $hash): void
    {
        $this->postAddress->setGeocodableHash($hash);
    }

    public function updatePostAddress(PostAddress $postAddress): void
    {
        if (!$this->postAddress || !$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }
}
