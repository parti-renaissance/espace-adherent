<?php

namespace App\Controller\Api\Zone;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route(path: '/v3/place')]
#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
class PlaceAutocompleteController extends AbstractController
{
    public function __construct(
        private readonly string $googlePlaceApiKey,
        private readonly HttpClientInterface $googlePlaceClient,
    ) {
    }

    #[Route(path: '/autocomplete', name: 'api_place_autocomplete', methods: ['GET'])]
    public function autocompleteAction(Request $request): Response
    {
        $response = [];

        if ($input = $request->query->get('input')) {
            $response = $this->googlePlaceClient->request('GET', 'autocomplete/json?language=fr&key='.$this->googlePlaceApiKey.'&input='.$input)->toArray();
        }

        return $this->json($response);
    }

    #[Route(path: '/details', name: 'api_place_details', methods: ['GET'])]
    public function detailsAction(Request $request): Response
    {
        $response = $this->googlePlaceClient->request('GET', 'details/json?language=fr&key='.$this->googlePlaceApiKey.'&place_id='.$request->query->get('place_id'));

        return $this->json($response->toArray());
    }
}
