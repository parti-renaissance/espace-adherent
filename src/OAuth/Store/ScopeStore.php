<?php

namespace AppBundle\OAuth\Store;

use AppBundle\OAuth\Model\Client;
use AppBundle\OAuth\Model\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeStore implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier)
    {
        if (!Scope::isValid($identifier)) {
            return null;
        }

        return new Scope($identifier);
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        if (!$clientEntity instanceof Client) {
            throw new \LogicException(sprintf('Only %s instances are supported', Client::class));
        }

        // Check if OAuth client asked for an un-granted scope (it's not done by the oauth server out of the box)
        $invalidScopes = array_diff(
            array_map(function (Scope $scope) {return $scope->getIdentifier(); }, $scopes), // Scopes asked by the client
            $clientEntity->getScopes() // Scopes allowed for the client
        );

        if ($invalidScopes) {
            throw OAuthServerException::invalidScope(implode(',', $invalidScopes));
        }

        return $scopes;
    }
}
