<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

/**
 * This CSRF Token manager generates simple token, without randomisation mechanism.
 * It provides shorter tokens, used on Paybox callbacks, which accept the URL callback until 150chars length.
 */
class SimpleCsrfTokenManager implements CsrfTokenManagerInterface
{
    public function __construct(
        private readonly TokenGeneratorInterface $generator,
        private readonly TokenStorageInterface $storage,
    ) {
    }

    public function getToken(string $tokenId): CsrfToken
    {
        if ($this->storage->hasToken($tokenId)) {
            $value = $this->storage->getToken($tokenId);
        } else {
            $value = $this->generator->generateToken();

            $this->storage->setToken($tokenId, $value);
        }

        return new CsrfToken($tokenId, $value);
    }

    public function refreshToken(string $tokenId): CsrfToken
    {
        $value = $this->generator->generateToken();

        $this->storage->setToken($tokenId, $value);

        return new CsrfToken($tokenId, $value);
    }

    public function removeToken(string $tokenId): ?string
    {
        return $this->storage->removeToken($tokenId);
    }

    public function isTokenValid(CsrfToken $token): bool
    {
        if (!$this->storage->hasToken($tokenId = $token->getId())) {
            return false;
        }

        return hash_equals($this->storage->getToken($tokenId), $token->getValue());
    }
}
