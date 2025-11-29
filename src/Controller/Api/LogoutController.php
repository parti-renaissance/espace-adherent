<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\OAuth\AccessTokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/logout', name: 'api_logout', methods: ['POST'])]
class LogoutController extends AbstractController
{
    public function __invoke(ServerRequestInterface $psrRequest, ResourceServer $resourceServer, AccessTokenRepository $authority): Response
    {
        try {
            $psrRequest = $resourceServer->validateAuthenticatedRequest($psrRequest);
        } catch (OAuthServerException $e) {
            return $this->json('OK');
        }

        if (!$tokenId = $psrRequest->getAttribute('oauth_access_token_id')) {
            return $this->json('OK');
        }

        if ($token = $authority->findAccessTokenByIdentifier($tokenId)) {
            $token->appSession?->refresh($psrRequest->getHeaderLine('User-Agent') ?: null, $psrRequest->getHeaderLine('X-App-Version') ?: null);
            $authority->revokeToken($token, true);
        }

        return $this->json('OK');
    }
}
