<?php

declare(strict_types=1);

namespace App\Controller\OAuth;

use App\OAuth\App\AuthAppUrlManager;
use App\Repository\OAuth\ClientRepository;
use App\Scope\ScopeEnum;
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

        $client = ($clientCode ? $clientRepository->findOneBy(['code' => $clientCode]) : null) ?? $clientRepository->getVoxClient();

        if ($client->isCadreClient() && str_contains($state = $request->query->get('state', ''), 'cartographie-electorale')) {
            $stateParams = [];
            parse_str(parse_url(urldecode($state), \PHP_URL_QUERY), $stateParams);

            return $this->redirectToRoute('eaggle_app_redirect', ['scope' => $stateParams['scope']]);
        }

        $isAdmin = $this->isGranted('IS_IMPERSONATOR');
        $supportedScopes = $client->getSupportedScopes();
        $scopesToUse = $isAdmin ? $client->getSupportedScopes(true) : $client->getUserScopes(true);

        if (($requestedScope = $request->query->get('scope')) && ScopeEnum::isValid($requestedScope)) {
            $requestedScope = 'scope:'.$requestedScope;
            if (\in_array($requestedScope, $supportedScopes, true)) {
                $scopesToUse[] = $requestedScope;
            }
        }

        $redirectUri = current($client->getRedirectUris());

        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => $isAdmin ? $this->adminRenaissanceHost : $urlGenerator->getAppHost(),
            'response_type' => 'code',
            'client_id' => $client->getUuid(),
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopesToUse),
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
