<?php

namespace App\Controller\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\OAuth\JWTTokenGenerator;
use App\Security\Voter\OAuthClientVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route(path: '/sso/{uuid}', name: 'app_front_oauth_sso', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class SsoController extends AbstractController
{
    public function __invoke(Request $request, Client $client, JWTTokenGenerator $tokenGenerator): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Adherent) {
            throw $this->createAccessDeniedException();
        }

        $this->denyAccessUnlessGranted(OAuthClientVoter::PERMISSION, $client);

        $clientRedirectUri = current($client->getRedirectUris());

        $queryParams = [
            'jwt' => $tokenGenerator->generate($user, $client),
            'return_to' => $request->query->get('return_to'),
        ];

        return $this->redirect($clientRedirectUri.'?'.http_build_query($queryParams));
    }
}
