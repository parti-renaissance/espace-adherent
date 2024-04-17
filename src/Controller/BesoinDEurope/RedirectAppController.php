<?php

namespace App\Controller\BesoinDEurope;

use App\OAuth\Model\Scope;
use App\Twig\OAuthClientRuntime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RedirectAppController extends AbstractController
{
    public function __invoke(string $userBesoinDEuropeHost, OAuthClientRuntime $authClientRuntime): Response
    {
        return $this->redirectToRoute('app_front_oauth_authorize', [
            'app_domain' => $userBesoinDEuropeHost,
            'response_type' => 'code',
            'client_id' => $authClientRuntime->getVoxClientId(),
            'scope' => implode(' ', [Scope::JEMARCHE_APP, Scope::READ_PROFILE, Scope::WRITE_PROFILE]),
        ]);
    }
}
