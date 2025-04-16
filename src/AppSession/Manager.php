<?php

namespace App\AppSession;

use App\Entity\AppSession;
use App\Repository\OAuth\AccessTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use Psr\Http\Message\ServerRequestInterface;

class Manager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AccessTokenRepository $accessTokenRepository,
    ) {
    }

    public function refreshSession(AccessTokenEntityInterface $token, ServerRequestInterface $request): void
    {
        if (!$tokenEntity = $this->accessTokenRepository->findAccessTokenByIdentifier($token->getIdentifier())) {
            return;
        }

        $session = new AppSession($tokenEntity->getUser(), $tokenEntity->getClient());
        if ($token->oldAccessTokenId && ($oldTokenEntity = $this->accessTokenRepository->findAccessTokenByIdentifier($token->oldAccessTokenId))) {
            $session = $oldTokenEntity->appSession && $oldTokenEntity->appSession->isActive() ? $oldTokenEntity->appSession : $session;
        }

        $session->refresh($request->getHeaderLine('User-Agent') ?: null, $request->getHeaderLine('X-App-Version') ?: $request->getQueryParams()['app-version'] ?? null);

        $tokenEntity->appSession = $session;

        $this->entityManager->flush();
    }
}
