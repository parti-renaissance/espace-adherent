<?php

declare(strict_types=1);

namespace App\Twig;

use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AnonymousRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function generateLoginPathForAnonymousFollower(string $callbackRoute = '', array $params = []): string
    {
        return $this->doGeneratePathForAnonymousFollower('/connexion', $callbackRoute, $params);
    }

    public function generateAdhesionPathForAnonymousFollower(string $callbackRoute = '', array $params = [], array $intentionRouteParams = []): string
    {
        return $this->doGeneratePathForAnonymousFollower('/adhesion'.($intentionRouteParams ? '?'.http_build_query($intentionRouteParams) : ''), $callbackRoute, $params);
    }

    private function doGeneratePathForAnonymousFollower(
        string $intention,
        string $callbackRoute = '',
        array $params = [],
    ): string {
        if (!$callbackRoute) {
            $callbackRoute = $this->requestStack->getMainRequest()->attributes->get('_route');
        }

        if (!$params) {
            $params = $this->requestStack->getMainRequest()->attributes->get('_route_params');
        }

        $params[AnonymousFollowerSession::AUTHENTICATION_INTENTION] = $intention;

        return $this->urlGenerator->generate($callbackRoute, $params);
    }
}
