<?php

namespace App\OAuth\Store;

use App\Entity\OAuth\AuthorizationCode as PersistentAuthorizationCode;
use App\OAuth\Model\AuthorizationCode as InMemoryAuthorizationCode;
use App\OAuth\PersistentTokenFactory;
use App\Repository\OAuth\AuthorizationCodeRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface as OAuthAuthCodeRepositoryInterface;

class AuthorizationCodeStore implements OAuthAuthCodeRepositoryInterface
{
    private $persistentTokenFactory;
    private $authorizationCodeRepository;

    public function __construct(
        PersistentTokenFactory $persistentTokenFactory,
        AuthorizationCodeRepository $authorizationCodeRepository
    ) {
        $this->persistentTokenFactory = $persistentTokenFactory;
        $this->authorizationCodeRepository = $authorizationCodeRepository;
    }

    public function getNewAuthCode()
    {
        return new InMemoryAuthorizationCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCode)
    {
        $this->store($this->persistentTokenFactory->createAuthorizationCode($authCode));
    }

    public function revokeAuthCode($codeId)
    {
        if (!$token = $this->findAuthorizationCode($codeId)) {
            return;
        }

        $token->revoke();
        $this->store($token);
    }

    public function isAuthCodeRevoked($codeId)
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
