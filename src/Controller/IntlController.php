<?php

declare(strict_types=1);

namespace App\Controller;

use App\FranceCities\FranceCities;
use libphonenumber\PhoneNumberUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;

class IntlController extends AbstractController
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    #[Route(path: '/postal-code/{postalCode}', name: 'api_postal_code', methods: ['GET'])]
    public function postalCodeAction(string $postalCode): JsonResponse
    {
        $cities = $this->franceCities->findCitiesByPostalCode($postalCode);

        $result = [];
        foreach ($cities as $city) {
            $result[$city->getInseeCode()] = $city->getName();
        }

        return new JsonResponse($result);
    }

    #[Route(path: '/countries', name: 'api_countries', methods: ['GET'])]
    public function countriesAction(): JsonResponse
    {
        $util = PhoneNumberUtil::getInstance();

        $countries = [];

        foreach (Countries::getNames() as $region => $name) {
            $countries[] = [
                'name' => $name,
                'region' => $region,
                'code' => $util->getCountryCodeForRegion($region),
            ];
        }

        return new JsonResponse($countries);
    }
}
