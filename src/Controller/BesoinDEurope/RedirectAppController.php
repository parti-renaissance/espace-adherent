<?php

namespace App\Controller\BesoinDEurope;

use App\BesoinDEurope\Inscription\FinishInscriptionRedirectHandler;
use App\OAuth\App\AuthAppUrlManager;
use App\Repository\OAuth\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAppController extends AbstractController
{
    public function __invoke(Request $request, AuthAppUrlManager $appUrlManager, string $userBesoinDEuropeHost, ClientRepository $clientRepository): Response
    {
        $currentApp = $appUrlManager->getAppCodeFromRequest($request);
        $urlGenerator = $appUrlManager->getUrlGenerator($currentApp);

        $client = $clientRepository->getVoxClient();
        $session = $request->getSession();

        if ($redirectUri = $session->get(FinishInscriptionRedirectHandler::SESSION_KEY)) {
            $session->remove(FinishInscriptionRedirectHandler::SESSION_KEY);

            return $this->redirect($redirectUri);
        }

        $redirectUri = current($client->getRedirectUris());

        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => $urlGenerator->getAppHost(),
            'response_type' => 'code',
            'client_id' => $client->getUuid(),
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $client->getSupportedScopes()),
        ]);
    }
}
