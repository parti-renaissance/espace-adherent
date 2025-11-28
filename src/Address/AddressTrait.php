<?php

declare(strict_types=1);

namespace App\Address;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait AddressTrait
{
    #[Assert\Length(max: 150, maxMessage: 'common.address.max_length')]
    protected ?string $address = null;

    #[Assert\Length(max: 150, maxMessage: 'common.address.max_length')]
    protected ?string $additionalAddress = null;

    #[Assert\Length(max: 15)]
    #[Assert\NotBlank(message: 'common.postal_code.not_blank')]
    protected ?string $postalCode = null;

    #[Assert\Length(max: 15)]
    protected ?string $city = null;

    #[Assert\Length(max: 255)]
    protected ?string $cityName = null;

    #[Assert\Country(message: 'common.country.invalid')]
    #[Assert\NotBlank]
    protected ?string $country = null;

    #[Assert\Length(max: 255)]
    #[Groups(['profile_write'])]
    protected ?string $region = null;

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getAdditionalAddress(): ?string
    {
        return $this->additionalAddress;
    }

    public function setAdditionalAddress(?string $additionalAddress): void
    {
        $this->additionalAddress = $additionalAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        if ($city) {
            $parts = explode('-', $city);
            if (2 !== \count($parts)) {
                throw new \InvalidArgumentException(\sprintf('Invalid french city format: %s.', $city));
            }

            if (!$this->postalCode) {
                $this->setPostalCode($parts[0]);
            }
        }

        $this->city = $city;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getCountryName(?string $locale = null): ?string
    {
        try {
            return $this->country ? Countries::getName($this->country, $locale) : null;
        } catch (MissingResourceException $e) {
        }

        return null;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }

    public function isFrenchAddress(): bool
    {
        return AddressInterface::FRANCE === $this->country && $this->city;
    }

    public function getGeocodableAddress(): string
    {
        return (string) GeocodableAddress::createFromAddress($this);
    }

    public function getFullAddress(): string
    {
        return $this->address ? implode(', ', array_filter([$this->address, $this->postalCode, $this->cityName, $this->country])) : '';
    }

    /**
     * Returns the french national INSEE code from the city code.
     */
    public function getInseeCode(): ?string
    {
        $inseeCode = null;
        if ($this->city && 5 === strpos($this->city, '-')) {
            [, $inseeCode] = explode('-', $this->city);
        }

        return $inseeCode;
    }

    public static function createFromAddress(AddressInterface $other): self
    {
        $address = new self();
        $address->address = $other->getAddress();
        $address->additionalAddress = $other->getAdditionalAddress();
        $address->postalCode = $other->getPostalCode();
        $address->city = $other->getCity();
        $address->cityName = $other->getCityName();
        $address->country = $other->getCountry();
        $address->region = $other->getRegion();

        return $address;
    }
}
