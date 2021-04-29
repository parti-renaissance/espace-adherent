<?php

namespace App\OAuth\Store;

use App\Entity\OAuth\TokenInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

trait TokenStoreTrait
{
    protected function checkToken(TokenInterface $token): void
    {
        if ($token->isExpired()) {
            throw OAuthServerException::invalidRefreshToken('Token has expired');
        }

        if ($token->isRevoked()) {
            throw OAuthServerException::invalidRefreshToken('Token has been revoked');
        }
    }
}
