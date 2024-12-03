<?php

namespace App\Controller\Api\Security;

use App\Entity\OAuth\Client;
use App\OAuth\JWTTokenGenerator;
use App\Security\Voter\OAuthClientVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[IsGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')]
#[Route(path: '/v3/sso/jwt/{uuid}', name: 'api_security_get_jwt_token', methods: ['GET'])]
class GetJWTTokenController extends AbstractController
{
    public function __invoke(Client $client, JWTTokenGenerator $tokenGenerator): Response
    {
        $this->denyAccessUnlessGranted(OAuthClientVoter::PERMISSION, $client);

        return $this->json(['token' => $tokenGenerator->generate($this->getUser(), $client)]);
    }
}
