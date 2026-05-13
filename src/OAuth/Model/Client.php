<?php

declare(strict_types=1);

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

final class Client implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    /**
     * @param string[] $scopes
     * @param string[] $allowedGrantTypes
     */
    public function __construct(
        string $identifier,
        private array $scopes,
        private array $allowedGrantTypes = [],
    ) {
        $this->setIdentifier($identifier);
        // Non-confidential: secret is optional and validated grant-by-grant in ClientStore::validateClient()
        // when the grant type requires it. league v9's AbstractGrant skips secret enforcement for non-confidential
        // clients, which preserves the legacy behaviour of allowing empty secrets on authorization_code.
        $this->isConfidential = false;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setRedirectUris(array $uris): void
    {
        $this->redirectUri = $uris;
    }

    public function setRedirectUri(string $uri): void
    {
        $this->setRedirectUris([$uri]);
    }

    public function getRedirectUris(): array
    {
        return $this->redirectUri;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function supportsGrantType(string $grantType): bool
    {
        return [] === $this->allowedGrantTypes || \in_array($grantType, $this->allowedGrantTypes, true);
    }
}
