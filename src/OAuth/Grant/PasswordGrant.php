<?php

namespace App\OAuth\Grant;

use League\OAuth2\Server\Grant\PasswordGrant as BasePasswordGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class PasswordGrant extends BasePasswordGrant
{
    use DeviceGrantTrait;

    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request, $client);
        $device = $this->validateDevice($request);

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        // Issue and persist new tokens
        $accessToken = $this->issueAccessTokenWithDevice(
            $accessTokenTTL,
            $client,
            $user->getIdentifier(),
            $device ? $device->getIdentifier() : null,
            $finalizedScopes
        );
        $refreshToken = $this->issueRefreshToken($accessToken);

        // Inject tokens into response
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }
}
