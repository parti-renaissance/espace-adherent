<?php

namespace AppBundle\Address;

use Symfony\Component\Intl\Intl;

final class GeocodableAddress
{
    private $address;
    private $postalCode;
    private $cityName;
    private $countryCode;

    public function __construct(string $address, string $postalCode, string $cityName, string $countryCode)
    {
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->cityName = $cityName;
        $this->countryCode = $countryCode;
    }

    public static function createFromAddress(AddressInterface $address): self
    {
        return new self(
            (string) $address->getAddress(),
            (string) $address->getPostalCode(),
            (string) $address->getCityName(),
            (string) $address->getCountry()
        );
    }

    public function __toString()
    {
        $address = [];
        if ($this->address) {
            $address[] = str_replace(',', '', $this->address);
        }

        if ($this->postalCode) {
            $address[] = sprintf('%s %s', $this->postalCode, $this->cityName);
        }

        $address[] = Intl::getRegionBundle()->getCountryName($this->countryCode, 'en');

        return implode(', ', $address);
    }
}
