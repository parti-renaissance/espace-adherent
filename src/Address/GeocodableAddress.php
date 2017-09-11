<?php

namespace AppBundle\Address;

use AppBundle\Intl\FranceCitiesBundle;

final class GeocodableAddress
{
    private $_address;
    private $_postalCode;
    private $_cityName;
    private $_countryCode;

    /**
     * GeocodableAddress constructor.
     * @param string $address
     * @param string $postalCode
     * @param string $cityName
     * @param string $countryCode
     */
    public function __construct(string $address, string $postalCode, string $cityName, string $countryCode)
    {
        $this->_address      = $address;
        $this->_postalCode   = $postalCode;
        $this->_cityName     = $cityName;
        $this->_countryCode  = $countryCode;
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
        if ($this->_address) {
            $address[] = str_replace(',', '', $this->_address);
        }

        if ($this->_postalCode) {
            $address[] = sprintf('%s %s', $this->_postalCode, $this->_cityName);
        }

        $countryCode = $this->_countryCode;
        if ('FR' === $countryCode) {
            $countryCode = FranceCitiesBundle::getCountryISOCode($this->_postalCode);
        }
        $address[] = $countryCode;

        return implode(', ', $address);
    }
}
