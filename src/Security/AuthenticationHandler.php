<?php

namespace App\Security;

use App\Coalition\CoalitionUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class AuthenticationHandler implements LogoutSuccessHandlerInterface
{
    private $urlGenerator;
    private $coalitionsAuthHost;
    private $coalitionUrlGenerator;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        string $coalitionsAuthHost,
        CoalitionUrlGenerator $coalitionUrlGenerator
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->coalitionsAuthHost = $coalitionsAuthHost;
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
    }

    public function onLogoutSuccess(Request $request)
    {
        if (0 === strpos($request->getHost(), $this->coalitionsAuthHost)) {
            return new RedirectResponse($this->coalitionUrlGenerator->generateHomepageLink());
        }

        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }
}
