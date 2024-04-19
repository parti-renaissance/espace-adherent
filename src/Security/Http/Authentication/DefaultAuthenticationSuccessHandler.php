<?php

namespace App\Security\Http\Authentication;

use App\BesoinDEurope\Inscription\FinishInscriptionRedirectHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler as BaseAuthenticationSuccesshandler;

class DefaultAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly BaseAuthenticationSuccesshandler $decorated,
        private readonly FinishInscriptionRedirectHandler $finishInscriptionRedirectHandler
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $response = $this->decorated->onAuthenticationSuccess($request, $token);

        if (
            $redirect = $this->finishInscriptionRedirectHandler->getBesoinDEuropeRedirect(
                $response instanceof RedirectResponse
                    ? $response->getTargetUrl()
                    : null
            )
        ) {
            return $redirect;
        }

        return $response;
    }
}
