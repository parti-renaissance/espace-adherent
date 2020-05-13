<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

final class Client implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    /**
     * @var array
     */
    private $scopes;

    public function __construct(string $identifier, array $scopes)
    {
        $this->setIdentifier($identifier);
        $this->scopes = $scopes;
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
}
