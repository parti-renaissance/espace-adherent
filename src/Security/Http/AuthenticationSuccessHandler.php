<?php

namespace App\Security\Http;

use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Contracts\Service\Attribute\Required;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private AnonymousFollowerSession $anonymousFollowerSession;

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        if (!$token instanceof NullToken && $this->anonymousFollowerSession->isStarted()) {
            return $this->anonymousFollowerSession->terminate();
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    #[Required]
    public function setAnonymousFollowerSession(AnonymousFollowerSession $anonymousFollowerSession): void
    {
        $this->anonymousFollowerSession = $anonymousFollowerSession;
    }
}
