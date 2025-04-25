<?php

namespace App\AppSession;

use App\Entity\AppSession;
use App\OAuth\Model\AccessToken;
use App\OAuth\Model\Scope;
use App\Repository\AppSessionRepository;
use App\Repository\OAuth\AccessTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use Psr\Http\Message\ServerRequestInterface;
use UAParser\Parser;

class Manager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AccessTokenRepository $accessTokenRepository,
        private readonly AppSessionRepository $appSessionRepository,
    ) {
    }

    public function refreshSession(AccessTokenEntityInterface $token, ServerRequestInterface $request): void
    {
        if (!$tokenEntity = $this->accessTokenRepository->findAccessTokenByIdentifier($token->getIdentifier())) {
            return;
        }

        if ($tokenEntity->hasScope(Scope::IMPERSONATOR)) {
            return;
        }

        $session = new AppSession($tokenEntity->getUser(), $tokenEntity->getClient());
        $appSystem = ($system = $request->getQueryParams()['system'] ?? null) ? SystemEnum::fromString($system) : null;
        $userAgent = $request->getQueryParams()['user-agent'] ?? $request->getHeaderLine('User-Agent') ?: null;

        // if refresh flow is used, we need to take the old session linked to the old access token
        if ($token instanceof AccessToken && $token->oldAccessTokenId && ($oldTokenEntity = $this->accessTokenRepository->findAccessTokenByIdentifier($token->oldAccessTokenId))) {
            $session = $oldTokenEntity->appSession && $oldTokenEntity->appSession->isActive() ? $oldTokenEntity->appSession : $session;
        } elseif (($previousSessionId = $request->getParsedBody()['session_id'] ?? null) && $previousSession = $this->appSessionRepository->findOneByUuid($previousSessionId)) {
            // if previous session id is provided, we can try to find the session and use it
            /** @var AppSession $previousSession */
            if ($previousSession->isActive() && $this->isSimilarSessions($previousSession, $session, $userAgent, $appSystem)) {
                $session = $previousSession;
            }
        }

        $session->refresh(
            $userAgent,
            $request->getHeaderLine('X-App-Version') ?: ($request->getQueryParams()['app-version'] ?? null),
            $appSystem,
        );

        $tokenEntity->appSession = $session;

        $this->entityManager->flush();
        $token->currentSessionUuid = $session->getUuid();
    }

    private function isSimilarSessions(AppSession $previousSession, AppSession $session, ?string $userAgent, ?SystemEnum $appSystem): bool
    {
        if (
            $previousSession->adherent === $session->adherent
            && $previousSession->client === $session->client
            && $previousSession->appSystem === $appSystem
        ) {
            $parser = Parser::create();
            $previousUa = $parser->parse($previousSession->userAgent ?? '');
            $currentUa = $parser->parse($userAgent ?? '');

            if ($previousUa->device->brand === $currentUa->device->brand && $previousUa->ua->family === $currentUa->ua->family) {
                return true;
            }
        }

        return false;
    }
}
