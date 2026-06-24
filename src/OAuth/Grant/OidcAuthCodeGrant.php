<?php

declare(strict_types=1);

namespace App\OAuth\Grant;

use App\OAuth\Model\AccessToken;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class OidcAuthCodeGrant extends AuthCodeGrant
{
    private ?string $capturedNonce = null;
    private ?string $decryptedNonce = null;

    public function __construct(
        private readonly ClientRepository $entityClientRepository,
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        \DateInterval $authCodeTTL,
        private readonly ?LoggerInterface $logger = null,
    ) {
        parent::__construct($authCodeRepository, $refreshTokenRepository, $authCodeTTL);
    }

    public function validateAuthorizationRequest(ServerRequestInterface $request): AuthorizationRequest
    {
        $authRequest = parent::validateAuthorizationRequest($request);

        $clientEntity = $this->entityClientRepository->findOneByUuid($authRequest->getClient()->getIdentifier());

        if (null !== $clientEntity && $clientEntity->isPkceRequired()) {
            $codeChallenge = $this->getQueryStringParameter('code_challenge', $request);
            if (null === $codeChallenge || '' === $codeChallenge) {
                throw OAuthServerException::invalidRequest('code_challenge', 'PKCE code_challenge is required for this client');
            }

            $codeChallengeMethod = $this->getQueryStringParameter('code_challenge_method', $request, 'plain');
            if ('S256' !== $codeChallengeMethod) {
                throw OAuthServerException::invalidRequest('code_challenge_method', 'PKCE code_challenge_method must be S256 for this client');
            }
        }

        $this->capturedNonce = $this->getQueryStringParameter('nonce', $request);

        return $authRequest;
    }

    /**
     * Override to inject the captured OIDC `nonce` into the encrypted auth code payload,
     * so it can be extracted back when exchanging the code for an access token.
     *
     * @param string $unencryptedData
     */
    protected function encrypt($unencryptedData): string
    {
        if (null !== $this->capturedNonce && \is_string($unencryptedData)) {
            $payload = json_decode($unencryptedData, true);
            if (\is_array($payload)) {
                $payload['nonce'] = $this->capturedNonce;
                $reEncoded = json_encode($payload);
                if (false !== $reEncoded) {
                    $unencryptedData = $reEncoded;
                }
            }
        }

        return parent::encrypt($unencryptedData);
    }

    /**
     * Override to extract the OIDC `nonce` from the decrypted auth code payload,
     * stash it for `issueAccessToken` to propagate onto the AccessToken.
     *
     * @param string $encryptedData
     */
    protected function decrypt($encryptedData): string
    {
        $decrypted = parent::decrypt($encryptedData);

        $payload = json_decode($decrypted, true);
        if (\is_array($payload) && isset($payload['nonce']) && \is_string($payload['nonce'])) {
            $this->decryptedNonce = $payload['nonce'];
        }

        if (null !== $this->logger && \is_array($payload) && isset($payload['auth_code_id'])) {
            $this->logger->info('OAuth token exchange: auth code redirect_uri trace', [
                'auth_code_id' => $payload['auth_code_id'],
                'stored_redirect_uri' => $payload['redirect_uri'] ?? null,
                'client_id' => $payload['client_id'] ?? null,
                'has_code_challenge' => !empty($payload['code_challenge']),
            ]);
        }

        return $decrypted;
    }

    protected function issueAccessToken(
        \DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = [],
    ) {
        $accessToken = parent::issueAccessToken($accessTokenTTL, $client, $userIdentifier, $scopes);

        if ($accessToken instanceof AccessToken && null !== $this->decryptedNonce) {
            $accessToken->nonce = $this->decryptedNonce;
        }

        return $accessToken;
    }
}
