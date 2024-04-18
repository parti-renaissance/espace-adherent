<?php

namespace App\Security;

use App\OAuth\App\AuthAppUrlManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private AuthAppUrlManager $appUrlManager;

    public function __construct(AuthAppUrlManager $appUrlManager)
    {
        $this->appUrlManager = $appUrlManager;
    }

    public function onLogoutSuccess(Request $request)
    {
        if ($currentApp = $this->appUrlManager->getAppCodeFromRequest($request)) {
            return new RedirectResponse($this->appUrlManager->getUrlGenerator($currentApp)->generateForLogoutSuccess());
        }

        return new RedirectResponse('/');
    }
}
