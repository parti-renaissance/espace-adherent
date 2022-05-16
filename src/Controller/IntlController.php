<?php

namespace App\Controller;

use App\FranceCities\FranceCities;
use App\Intl\VoteOfficeBundle;
use libphonenumber\PhoneNumberUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Annotation\Route;

class IntlController extends AbstractController
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    /**
     * @Route("/postal-code/{postalCode}", name="api_postal_code", methods={"GET"})
     */
    public function postalCodeAction(string $postalCode): JsonResponse
    {
        return new JsonResponse($this->franceCities->getPostalCodeCities($postalCode));
    }

    /**
     * @Route("/vote-offices/{countryCode}", name="api_vote_offices", methods={"GET"})
     */
    public function voteOfficesAction(string $countryCode): JsonResponse
    {
        return new JsonResponse(VoteOfficeBundle::getVoteOfficies($countryCode));
    }

    /**
     * @Route("/countries", name="api_countries", methods={"GET"})
     */
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
