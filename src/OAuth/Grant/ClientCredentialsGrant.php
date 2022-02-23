<?php

namespace App\OAuth\Grant;

use League\OAuth2\Server\Grant\ClientCredentialsGrant as BaseClientCredentialsGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientCredentialsGrant extends BaseClientCredentialsGrant
{
    use DeviceGrantTrait;

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $device = $this->validateDevice($request);

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client);

        // Issue and persist access token
        $accessToken = $this->issueAccessTokenWithDevice(
            $accessTokenTTL,
            $client,
            null,
            $device ? $device->getIdentifier() : null,
            $finalizedScopes
        );

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }
}
