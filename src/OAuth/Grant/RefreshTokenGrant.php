<?php

declare(strict_types=1);

namespace App\OAuth\Grant;

use App\OAuth\Model\Scope;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\RefreshTokenGrant as BaseRefreshTokenGrant;
use Psr\Http\Message\ServerRequestInterface;

class RefreshTokenGrant extends BaseRefreshTokenGrant
{
    private ?string $oldAccessTokenId = null;
    public ?int $adminSessionTtl = null;

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

        if (\in_array(Scope::IMPERSONATOR, $oldRefreshToken['scopes'] ?? [], true)) {
            $estimatedAgeInSeconds = new \DateTimeImmutable()->add($this->refreshTokenTTL)->getTimestamp() - (int) $oldRefreshToken['expire_time'];

            if ($estimatedAgeInSeconds > $this->adminSessionTtl) {
                throw OAuthServerException::invalidRefreshToken('Token has expired');
            }
        }

        $this->oldAccessTokenId = $oldRefreshToken['access_token_id'] ?? null;

        return $oldRefreshToken;
    }
}
