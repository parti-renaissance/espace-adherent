<?php

namespace AppBundle\Security\Http;

use AppBundle\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AnonymousFollowerAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private $anonymousFollowerSession;

    public function __construct(
        HttpUtils $httpUtils,
        array $options = [],
        AnonymousFollowerSession $anonymousFollowerSession
    ) {
        parent::__construct($httpUtils, $options);

        $this->anonymousFollowerSession = $anonymousFollowerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if (!$token instanceof AnonymousToken && $this->anonymousFollowerSession->isStarted()) {
            return $this->anonymousFollowerSession->terminate();
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
