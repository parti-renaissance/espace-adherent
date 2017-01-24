<?php

namespace AppBundle\Address;

interface AddressInterface
{
    /**
     * Returns the street name.
     *
     * @return string
     */
    public function getAddress();

    /**
     * Returns the zip code.
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Returns the city name.
     *
     * @return string
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
     * Returns the 2 char country code.
     *
     * For example: FR
     *
     * @return string
     */
    public function getCountry();
}
