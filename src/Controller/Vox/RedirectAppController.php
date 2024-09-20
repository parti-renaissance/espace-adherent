<?php

namespace App\Controller\Vox;

use App\OAuth\App\AuthAppUrlManager;
use App\Repository\OAuth\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAppController extends AbstractController
{
    public function __construct(private readonly string $adminRenaissanceHost)
    {
    }

    public function __invoke(Request $request, AuthAppUrlManager $appUrlManager, ClientRepository $clientRepository): Response
    {
        $currentApp = $appUrlManager->getAppCodeFromRequest($request);
        $urlGenerator = $appUrlManager->getUrlGenerator($currentApp ?? '');

        $client = $clientRepository->getVoxClient();

        $redirectUri = current($client->getRedirectUris());

        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => $this->isGranted('ROLE_PREVIOUS_ADMIN') ? $this->adminRenaissanceHost : $urlGenerator?->getAppHost(),
            'response_type' => 'code',
            'client_id' => $client->getUuid(),
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $client->getSupportedScopes()),
        ]);
    }
}
