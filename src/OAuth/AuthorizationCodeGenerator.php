<?php

declare(strict_types=1);

namespace App\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class AuthorizationCodeGenerator
{
    public function __construct(
        private readonly AuthorizationServer $authorizationServer,
        private readonly ClientRepository $clientRepository,
        private readonly HttpMessageFactoryInterface $httpMessageFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function generate(Adherent $user, string $codeChallenge, string $clientId, string $redirectUri): ?string
    {
        if (!Uuid::isValid($clientId)) {
            return null;
        }

        $client = $this->clientRepository->findOneByUuid($clientId);

        if (!$client instanceof Client) {
            return null;
        }

        $authorizeRequest = Request::create('/oauth/v2/auth', Request::METHOD_GET, [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $client->getUserScopes(true)),
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        try {
            $authRequest = $this->authorizationServer->validateAuthorizationRequest(
                $this->httpMessageFactory->createRequest($authorizeRequest)
            );
            $authRequest->setUser($user->getOAuthUser());
            $authRequest->setAuthorizationApproved(true);

            $response = $this->authorizationServer->completeAuthorizationRequest($authRequest, new Response());
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to mint an authorization code after signup activation.', [
                'adherent' => $user->getUuidAsString(),
                'exception' => $exception,
            ]);

            return null;
        }

        $location = $response->getHeaderLine('location');
        $separatorPosition = strpos($location, '?');
        $queryString = false === $separatorPosition ? '' : substr($location, $separatorPosition + 1);
        parse_str($queryString, $params);
        $code = $params['code'] ?? null;

        if (!\is_string($code) || '' === $code) {
            $this->logger->error('No authorization code in the authorize response after signup activation.', [
                'adherent' => $user->getUuidAsString(),
                'status' => $response->getStatusCode(),
            ]);

            return null;
        }

        return $code;
    }
}
