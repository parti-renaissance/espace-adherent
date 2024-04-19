<?php

namespace App\Controller\BesoinDEurope;

use App\AppCodeEnum;
use App\Entity\OAuth\Client;
use App\Repository\OAuth\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RedirectAppController extends AbstractController
{
    public function __invoke(string $userBesoinDEuropeHost, ClientRepository $clientRepository): Response
    {
        /** @var Client $client */
        $client = $clientRepository->findOneBy(['code' => AppCodeEnum::BESOIN_D_EUROPE]);

        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => $userBesoinDEuropeHost,
            'response_type' => 'code',
            'client_id' => $client->getUuid(),
            'redirect_uri' => current($client->getRedirectUris()),
            'scope' => implode(' ', $client->getSupportedScopes()),
        ]);
    }
}
