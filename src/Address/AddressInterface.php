<?php

declare(strict_types=1);

namespace App\Address;

interface AddressInterface
{
    public const FRANCE = 'FR';
    public const DEFAULT_TIME_ZONE = 'Europe/Paris';

    public const FRENCH_CODES = [
        0 => 'FR', // FR
        '971' => 'GP', // Guadeloupe
        '972' => 'MQ', // Martinique
        '973' => 'GF', // Guyane
        '974' => 'RE', // Réunion
        '975' => 'PM', // Saint-Pierre-et-Miquelon
        '976' => 'YT', // Mayotte
        '977' => 'BL', // Saint-Barthélemy
        '978' => 'MF', // Saint-Martin
        '986' => 'WF', // Wallis-et-Futuna
        '987' => 'PF', // Polynésie
        '988' => 'NC', // Nouvelle-Calédonie
    ];

    /**
     * Returns the street name.
     */
    public function getAddress(): ?string;

    public function getAdditionalAddress(): ?string;

    /**
     * Returns the zip code.
     */
    public function getPostalCode(): ?string;

    /**
     * Returns the city name.
     */
    public function getCityName(): ?string;

    public function setCityName(?string $cityName): void;

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

    public function getCountryName(?string $locale = null): ?string;

    public function getRegion(): ?string;
}
