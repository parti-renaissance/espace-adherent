<?php

namespace App\FranceCities;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;

class CitiesStorage
{
    public const EXCLUDE_INSEE_CODE = ['75056', '13055', '69123'];

    public static $countries = [
        '98000' => 'MC', // Monaco
        '971' => 'GP', // Guadeloupe
        '972' => 'MQ', // Martinique
        '973' => 'GF', // Guyane
        '974' => 'RE', // Réunion
        '975' => 'PM', // Saint-Pierre-et-Miquelon
        '976' => 'YT', // Mayotte
        '986' => 'WF', // Wallis-et-Futuna
        '987' => 'PF', // Polynésie
        '988' => 'NC', // Nouvelle Calédonie
    ];

    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function getCitiesList(): array
    {
        $zones = $this->zoneRepository->findBy(['type' => [Zone::CITY, Zone::BOROUGH]]);

        $cities = [];
        foreach ($zones as $zone) {
            if (\in_array($zone->getCode(), self::EXCLUDE_INSEE_CODE)) {
                continue;
            }

            if (\count($zone->getPostalCode()) > 1) {
                foreach ($zone->getPostalCode() as $postalCode) {
                    $cities[$zone->getCode()] = ['name' => $zone->getName(), 'postal_code' => $postalCode];
                }
                continue;
            }
            $cities[$zone->getCode()] = ['name' => $zone->getName(), 'postal_code' => $zone->getPostalCodeAsString()];
        }

        return $cities;
    }
}
