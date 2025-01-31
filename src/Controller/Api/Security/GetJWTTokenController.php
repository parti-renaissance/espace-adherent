<?php

namespace App\Controller\Api\Security;

use App\Entity\OAuth\Client;
use App\OAuth\JWTTokenGenerator;
use App\Security\Voter\OAuthClientVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'featurebase')"))]
#[Route(path: '/v3/sso/jwt/{uuid}', name: 'api_security_get_jwt_token', methods: ['GET'])]
class GetJWTTokenController extends AbstractController
{
    public function __invoke(Client $client, JWTTokenGenerator $tokenGenerator): Response
    {
        $this->denyAccessUnlessGranted(OAuthClientVoter::PERMISSION, $client);

        return $this->json(['token' => $tokenGenerator->generate($this->getUser(), $client)]);
    }
}
