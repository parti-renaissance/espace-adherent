<?php

declare(strict_types=1);

namespace App\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client as EntityClient;
use App\OAuth\Model\Client as InMemoryClient;
use App\OAuth\ResponseType\OidcBearerResponse;
use App\OAuth\Store\AccessTokenStore;
use App\OAuth\Store\RefreshTokenStore;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class DirectTokenIssuer
{
    public function __construct(
        private readonly AccessTokenStore $accessTokenStore,
        private readonly RefreshTokenStore $refreshTokenStore,
        private readonly OidcBearerResponse $bearerResponsePrototype,
        private readonly CryptKey $privateKey,
        private readonly string $encryptionKey,
        private readonly string $accessTokenTtlInterval,
        private readonly string $refreshTokenTtlInterval,
    ) {
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     */
    public function issue(EntityClient $client, ?Adherent $user, array $scopes): ResponseInterface
    {
        $userIdentifier = $user?->getUuid()->toString();

        $inMemoryClient = new InMemoryClient($client->getUuid()->toString(), $client->getSupportedScopes());
        $inMemoryClient->setName($client->getName());
        $inMemoryClient->setRedirectUris($client->getRedirectUris());

        $accessToken = $this->accessTokenStore->getNewToken($inMemoryClient, $scopes, $userIdentifier);
        $accessToken->setIdentifier(bin2hex(random_bytes(40)));
        $accessToken->setExpiryDateTime(
            new \DateTimeImmutable()->add(new \DateInterval($this->accessTokenTtlInterval))
        );
        $accessToken->setPrivateKey($this->privateKey);
        $this->accessTokenStore->persistNewAccessToken($accessToken);

        $refreshToken = $this->refreshTokenStore->getNewRefreshToken();
        $refreshToken->setAccessToken($accessToken);
        $refreshToken->setIdentifier(bin2hex(random_bytes(40)));
        $refreshToken->setExpiryDateTime(
            new \DateTimeImmutable()->add(new \DateInterval($this->refreshTokenTtlInterval))
        );
        $this->refreshTokenStore->persistNewRefreshToken($refreshToken);

        $response = clone $this->bearerResponsePrototype;
        $response->setPrivateKey($this->privateKey);
        $response->setEncryptionKey($this->encryptionKey);
        $response->setAccessToken($accessToken);
        $response->setRefreshToken($refreshToken);

        return $response->generateHttpResponse(new Response());
    }
}
