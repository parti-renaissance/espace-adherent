<?php

namespace AppBundle\OAuth\Store;

use AppBundle\OAuth\Model\Client;
use AppBundle\OAuth\Model\Scope as InMemoryScope;
use AppBundle\OAuth\Model\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeStore implements ScopeRepositoryInterface
{
    private static $supportedScopes = [
        'public',
        'user_profile',
        'web_hook',
    ];

    public function getScopeEntityByIdentifier($identifier)
    {
        if (!in_array($identifier, static::$supportedScopes, true)) {
            return null;
        }

        return new InMemoryScope($identifier);
    }

    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        if (!$clientEntity instanceof Client) {
            throw new \LogicException(sprintf('Only %s instances are supported', Client::class));
        }

        $unvalidScopes = array_diff(
            array_map(function(Scope $scope) {return $scope->getIdentifier();}, $scopes), // Scopes asked by the client
            $clientEntity->getScopes() // Scopes allowed for the client
        );

        if ($unvalidScopes) {
            throw OAuthServerException::invalidScope(implode(',', $unvalidScopes));
        }

        return $scopes;
    }
}
