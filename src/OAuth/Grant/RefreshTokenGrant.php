<?php

namespace App\OAuth\Grant;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Grant\RefreshTokenGrant as BaseRefreshTokenGrant;
use Psr\Http\Message\ServerRequestInterface;

class RefreshTokenGrant extends BaseRefreshTokenGrant
{
    private ?string $oldAccessTokenId = null;

    protected function issueAccessToken(
        \DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = [],
    ): AccessTokenEntityInterface {
        $accessToken = parent::issueAccessToken(
            $accessTokenTTL,
            $client,
            $userIdentifier,
            $scopes
        );

        $accessToken->oldAccessTokenId = $this->oldAccessTokenId;

        return $accessToken;
    }

    protected function validateOldRefreshToken(ServerRequestInterface $request, $clientId): array
    {
        $oldRefreshToken = parent::validateOldRefreshToken($request, $clientId);

        $this->oldAccessTokenId = $oldRefreshToken['access_token_id'] ?? null;

        return $oldRefreshToken;
    }
}
