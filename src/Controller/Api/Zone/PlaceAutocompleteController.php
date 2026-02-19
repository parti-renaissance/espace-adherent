<?php

declare(strict_types=1);

namespace App\Controller\Api\Zone;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
#[Route(path: '/v3/place')]
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

        if ($input = trim($request->query->get('input'))) {
            $input = urlencode(substr($input, 0, 150));
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
