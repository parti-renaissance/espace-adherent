<?php

declare(strict_types=1);

namespace App\OAuth\Store;

use App\Entity\OAuth\AuthorizationCode as PersistentAuthorizationCode;
use App\OAuth\Model\AuthorizationCode as InMemoryAuthorizationCode;
use App\OAuth\PersistentTokenFactory;
use App\Repository\OAuth\AuthorizationCodeRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface as OAuthAuthCodeRepositoryInterface;

class AuthorizationCodeStore implements OAuthAuthCodeRepositoryInterface
{
    public function __construct(
        private readonly PersistentTokenFactory $persistentTokenFactory,
        private readonly AuthorizationCodeRepository $authorizationCodeRepository,
    ) {
    }

    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new InMemoryAuthorizationCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $this->store($this->persistentTokenFactory->createAuthorizationCode($authCodeEntity));
    }

    public function revokeAuthCode(string $codeId): void
    {
        if (!$token = $this->findAuthorizationCode($codeId)) {
            return;
        }

        $token->revoke();
        $this->store($token);
    }

    public function isAuthCodeRevoked(string $codeId): bool
    {
        if (!$token = $this->findAuthorizationCode($codeId)) {
            return true;
        }

        return $token->isRevoked();
    }

    private function findAuthorizationCode(string $identifier): ?PersistentAuthorizationCode
    {
        return $this->authorizationCodeRepository->findAuthorizationCodeByIdentifier($identifier);
    }

    private function store(PersistentAuthorizationCode $token): void
    {
        $this->authorizationCodeRepository->save($token);
    }
}
