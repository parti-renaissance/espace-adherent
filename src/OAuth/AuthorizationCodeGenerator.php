<?php

declare(strict_types=1);

namespace App\OAuth;

use App\Entity\Adherent;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationCodeGenerator
{
    public function __construct(
        private readonly AuthorizationServer $authorizationServer,
        private readonly ClientRepository $clientRepository,
        private readonly HttpMessageFactoryInterface $httpMessageFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function generate(Adherent $user, string $codeChallenge): ?string
    {
        $client = $this->clientRepository->getVoxClient();
        $redirectUri = current($client->getRedirectUris());

        if (false === $redirectUri) {
            return null;
        }

        $authorizeRequest = Request::create('/oauth/v2/auth', Request::METHOD_GET, [
            'response_type' => 'code',
            'client_id' => (string) $client->getUuid(),
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

        parse_str((string) parse_url($response->getHeaderLine('location'), \PHP_URL_QUERY), $params);
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
