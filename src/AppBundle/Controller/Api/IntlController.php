<?php

namespace AppBundle\Controller\Api;

use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Intl\VoteOfficeBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class IntlController extends Controller
{
    /**
     * @Route("/postal-code/{postalCode}", defaults={"_enable_campaign_silence"=true}, name="api_postal_code")
     * @Method("GET")
     */
    public function postalCodeAction(string $postalCode): JsonResponse
    {
        return new JsonResponse(FranceCitiesBundle::getPostalCodeCities($postalCode));
    }

    /**
     * @Route("/vote-offices/{countryCode}", defaults={"_enable_campaign_silence"=true}, name="api_vote_offices")
     * @Method("GET")
     */
    public function voteOfficesAction(string $countryCode): JsonResponse
    {
        return new JsonResponse(VoteOfficeBundle::getVoteOfficies($countryCode));
    }
}
