<?php

namespace App\Controller\BesoinDEurope;

use App\BesoinDEurope\Inscription\FinishInscriptionRedirectHandler;
use App\Controller\BesoinDEurope\Inscription\InscriptionController;
use App\Repository\OAuth\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAppController extends AbstractController
{
    public function __invoke(Request $request, string $userBesoinDEuropeHost, ClientRepository $clientRepository): Response
    {
        $client = $clientRepository->getVoxClient();

        $session = $request->getSession();

        if ($redirectUri = $session->get(FinishInscriptionRedirectHandler::SESSION_KEY)) {
            $session->remove(FinishInscriptionRedirectHandler::SESSION_KEY);
        } elseif ($redirectUri = $session->get(InscriptionController::REDIRECT_PATH_KEY)) {
            $session->remove(InscriptionController::REDIRECT_PATH_KEY);
        } else {
            $redirectUri = current($client->getRedirectUris());
        }

        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => $userBesoinDEuropeHost,
            'response_type' => 'code',
            'client_id' => $client->getUuid(),
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $client->getSupportedScopes()),
        ]);
    }
}
