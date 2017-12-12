<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Redirects the user to auth logout on logout success.
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private $logoutPath;

    public function __construct(string $logoutPath)
    {
        $this->logoutPath = $logoutPath;
    }

    public function onLogoutSuccess(Request $request)
    {
        $url = sprintf('%s?redirect_uri=%s', $this->logoutPath, $request->getSchemeAndHttpHost());

        return new RedirectResponse($url);
    }
}
