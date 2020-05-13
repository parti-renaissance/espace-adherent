<?php

namespace App\Controller\Api;

use App\Intl\FranceCitiesBundle;
use App\Intl\VoteOfficeBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IntlController extends Controller
{
    /**
     * @Route("/postal-code/{postalCode}", name="api_postal_code", methods={"GET"})
     */
    public function postalCodeAction(string $postalCode): JsonResponse
    {
        return new JsonResponse(FranceCitiesBundle::getPostalCodeCities($postalCode));
    }

    /**
     * @Route("/vote-offices/{countryCode}", name="api_vote_offices", methods={"GET"})
     */
    public function voteOfficesAction(string $countryCode): JsonResponse
    {
        return new JsonResponse(VoteOfficeBundle::getVoteOfficies($countryCode));
    }

    /**
     * @Route("/city/autocompletion",
     *     name="api_city_autocomplete",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function cityAutocompleteAction(Request $request): JsonResponse
    {
        if (!$search = $request->query->get('search')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(FranceCitiesBundle::searchCities($search));
    }
}
