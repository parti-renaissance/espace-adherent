<?php

namespace AppBundle\Address;

interface AddressInterface
{
    /**
     * Returns the street name.
     *
     * @return string|null
     */
    public function getAddress();

    /**
     * Returns the zip code.
     *
     * @return string|null
     */
    public function getPostalCode();

    /**
     * Returns the city name.
     *
     * @return string|null
     */
    public function getCityName();

    /**
     * Returns the french city representation (zip code + insee code).
     *
     * For example: 92110-92024
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Returns the 2 chars country code.
     *
     * For example: FR
     *
     * @return string|null
     */
    public function getCountry();
}
