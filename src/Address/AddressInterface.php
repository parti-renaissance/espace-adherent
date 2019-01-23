<?php

namespace AppBundle\Address;

interface AddressInterface
{
    /**
     * Returns the street name.
     */
    public function getAddress(): ?string;

    /**
     * Returns the zip code.
     */
    public function getPostalCode(): ?string;

    /**
     * Returns the city name.
     */
    public function getCityName(): ?string;

    /**
     * Returns the french city representation (zip code + insee code).
     *
     * For example: 92110-92024
     */
    public function getCity(): ?string;

    /**
     * Returns the 2 chars country code.
     *
     * For example: FR
     */
    public function getCountry(): ?string;

    public function getRegion(): ?string;
}
