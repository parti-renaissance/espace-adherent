<?php

namespace App\Controller\OAuth;

use App\AppCodeEnum;
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

    public function __invoke(Request $request, AuthAppUrlManager $appUrlManager, ClientRepository $clientRepository, ?string $clientCode = null): Response
    {
        $currentApp = $appUrlManager->getAppCodeFromRequest($request);
        $urlGenerator = $appUrlManager->getUrlGenerator($currentApp ?? '');

        $client = match ($clientCode) {
            AppCodeEnum::JEMENGAGE_WEB => $clientRepository->getCadreClient(),
            AppCodeEnum::FORMATION => $clientRepository->getFormationClient(),
            default => $clientRepository->getVoxClient(),
        };

        $redirectUri = current($client->getRedirectUris());

        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => ($isAdmin = $this->isGranted('IS_IMPERSONATOR')) ? $this->adminRenaissanceHost : $urlGenerator->getAppHost(),
            'response_type' => 'code',
            'client_id' => $client->getUuid(),
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $isAdmin ? $client->getSupportedScopes() : $client->getUserScopes()),
            'state' => $request->query->get('state'),
        ]);
    }

    public function redirectToState(Request $request): Response
    {
        $path = $request->getPathInfo();
        if (str_starts_with($path, '/app')) {
            $state = substr($request->getPathInfo(), 4);
        }

        return $this->redirectToRoute('vox_app_redirect', ['state' => $state ?? null]);
    }
}
