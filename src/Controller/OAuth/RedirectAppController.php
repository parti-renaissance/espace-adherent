<?php

declare(strict_types=1);

namespace App\Controller\OAuth;

use App\OAuth\App\AuthAppUrlManager;
use App\Repository\OAuth\ClientRepository;
use App\Scope\ScopeGeneratorResolver;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAppController extends AbstractController
{
    public function __construct(
        private readonly string $adminRenaissanceHost,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request, AuthAppUrlManager $appUrlManager, ClientRepository $clientRepository, ?string $clientCode = null): Response
    {
        $currentApp = $appUrlManager->getAppCodeFromRequest($request);
        $urlGenerator = $appUrlManager->getUrlGenerator($currentApp ?? '');

        $client = ($clientCode ? $clientRepository->findOneBy(['code' => $clientCode]) : null) ?? $clientRepository->getVoxClient();
        $isAdmin = $this->isGranted('IS_IMPERSONATOR');

        if ($client->isCadreClient() && str_contains($state = $request->query->get('state', ''), 'cartographie-electorale')) {
            $stateParams = [];
            parse_str(parse_url(urldecode($state), \PHP_URL_QUERY), $stateParams);

            return $this->redirectToRoute('elecmap_app_redirect', [
                'app_domain' => $isAdmin ? $this->adminRenaissanceHost : $urlGenerator->getAppHost(),
                'scope' => $stateParams['scope'],
            ]);
        }

        $supportedScopes = $client->getSupportedScopes();
        $scopesToUse = $isAdmin ? $client->getSupportedScopes(true) : $client->getUserScopes(true);

        if ($scopeGenerator = $this->scopeGeneratorResolver->resolve()) {
            $requestedScope = 'scope:'.$scopeGenerator->getCode();
            if (\in_array($requestedScope, $supportedScopes, true)) {
                $scopesToUse[] = $requestedScope;
            }
        }

        $redirectUri = $clientCode
            ? current($client->getRedirectUris())
            : $this->resolveRedirectUriForSpaHost($client->getRedirectUris(), $urlGenerator->getSpaHost(), $currentApp);

        $this->logger->info('OAuth app-redirect re-issuing authorize', [
            'resolved_redirect_uri' => $redirectUri,
            'app_code' => $currentApp,
            'client_code' => $clientCode,
            'user_agent' => $request->headers->get('User-Agent'),
            'referer' => $request->headers->get('Referer'),
            'state' => $request->query->get('state'),
        ]);

        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => $isAdmin ? $this->adminRenaissanceHost : $urlGenerator->getAppHost(),
            'response_type' => 'code',
            'client_id' => $client->getUuid(),
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopesToUse),
            'state' => $request->query->get('state'),
        ]);
    }

    private function resolveRedirectUriForSpaHost(array $redirectUris, string $spaHost, ?string $appCode): string
    {
        $fallback = current($redirectUris) ?: '';

        if ('' === $spaHost) {
            return $fallback;
        }

        $matches = array_values(array_filter($redirectUris, static function (string $uri) use ($spaHost): bool {
            $parts = parse_url($uri);

            if (empty($parts['scheme']) || empty($parts['host']) || !\in_array($parts['scheme'], ['http', 'https'], true)) {
                return false;
            }

            return $parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '') === $spaHost;
        }));

        if (1 === \count($matches)) {
            return $matches[0];
        }

        if (\count($matches) > 1) {
            $this->logger->warning('Multiple redirect URIs match the SPA host; using the first match.', [
                'spa_host' => $spaHost,
                'app_code' => $appCode,
                'matches' => $matches,
            ]);

            return $matches[0];
        }

        $this->logger->warning('No redirect URI matches the SPA host; falling back to the first registered URI.', [
            'spa_host' => $spaHost,
            'app_code' => $appCode,
        ]);

        return $fallback;
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
