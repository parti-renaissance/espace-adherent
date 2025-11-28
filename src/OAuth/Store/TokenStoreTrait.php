<?php

declare(strict_types=1);

namespace App\OAuth\Store;

use App\Entity\OAuth\TokenInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

trait TokenStoreTrait
{
    protected function checkIfTokenIsAlreadyRevoked(TokenInterface $token): void
    {
        if ($token->isRevoked()) {
            throw OAuthServerException::invalidRefreshToken('Token has already been revoked');
        }
    }
}
