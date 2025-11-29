<?php

declare(strict_types=1);

namespace App\Security\Http\LoginLink\Authentication;

use App\BesoinDEurope\Inscription\FinishInscriptionRedirectHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler as BaseAuthenticationSuccessHandler;

class DefaultAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly BaseAuthenticationSuccessHandler $decorated,
        private readonly FinishInscriptionRedirectHandler $finishInscriptionRedirectHandler,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $response = $this->decorated->onAuthenticationSuccess($request, $token);

        if ($redirect = $this->finishInscriptionRedirectHandler->redirectToCompleteInscription(
            $response instanceof RedirectResponse
                ? $response->getTargetUrl()
                : null
        )) {
            return $redirect;
        }

        return $response;
    }
}
