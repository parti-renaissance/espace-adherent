<?php

declare(strict_types=1);

namespace App\OAuth\Store;

use App\Entity\OAuth\AuthorizationCode as PersistentAuthorizationCode;
use App\OAuth\Model\AuthorizationCode as InMemoryAuthorizationCode;
use App\OAuth\PersistentTokenFactory;
use App\Repository\OAuth\AuthorizationCodeRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface as OAuthAuthCodeRepositoryInterface;
use Psr\Log\LoggerInterface;

class AuthorizationCodeStore implements OAuthAuthCodeRepositoryInterface
{
    public function __construct(
        private readonly PersistentTokenFactory $persistentTokenFactory,
        private readonly AuthorizationCodeRepository $authorizationCodeRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getNewAuthCode()
    {
        return new InMemoryAuthorizationCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCode)
    {
        $this->logger->info('OAuth auth code minted', [
            'auth_code_id' => $authCode->getIdentifier(),
            'redirect_uri' => $authCode->getRedirectUri(),
            'client_id' => $authCode->getClient()->getIdentifier(),
        ]);

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
