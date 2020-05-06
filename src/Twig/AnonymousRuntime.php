<?php

namespace App\Twig;

use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AnonymousRuntime implements RuntimeExtensionInterface
{
    private const USER_LOGIN_ROUTE = 'app_user_login';
    private const USER_REGISTER_ROUTE = 'app_membership_join';

    private $urlGenerator;
    private $requestStack;

    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public function generateLoginPathForAnonymousFollower(string $callbackRoute = '', array $params = []): string
    {
        return $this->doGeneratePathForAnonymousFollower(self::USER_LOGIN_ROUTE, $callbackRoute, $params);
    }

    public function generateRegisterPathForAnonymousFollower(string $callbackRoute = '', array $params = []): string
    {
        return $this->doGeneratePathForAnonymousFollower(self::USER_REGISTER_ROUTE, $callbackRoute, $params);
    }

    private function doGeneratePathForAnonymousFollower(
        string $intention,
        string $callbackRoute = '',
        array $params = []
    ): string {
        if (!$callbackRoute) {
            $callbackRoute = $this->requestStack->getMasterRequest()->attributes->get('_route');
        }

        if (!$params) {
            $params = $this->requestStack->getMasterRequest()->attributes->get('_route_params');
        }

        $params[AnonymousFollowerSession::AUTHENTICATION_INTENTION] = $this->urlGenerator->generate($intention);

        return $this->urlGenerator->generate($callbackRoute, $params);
    }
}
