<?php

declare(strict_types=1);

namespace App\OAuth\ResponseType;

use App\OAuth\Model\Scope;
use App\OAuth\Oidc\JwkSetProvider;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Builder;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;

class OidcBearerResponse extends IdTokenResponse
{
    public function __construct(
        IdentityProviderInterface $identityProvider,
        ClaimExtractor $claimExtractor,
        JwkSetProvider $jwkSetProvider,
        private readonly string $issuer,
    ) {
        parent::__construct($identityProvider, $claimExtractor, $jwkSetProvider->getKid());
    }

    /**
     * Builds the extra params surfaced in the token endpoint JSON response,
     * alongside `access_token`, `refresh_token` and `expires_in`.
     *
     * Two concerns are multiplexed here:
     *
     * 1. OIDC `id_token` (scope=openid): parent::getExtraParams() delegates
     *    to the steverhoades library, which builds a signed JWT carrying the
     *    OIDC claims. The issuer + nonce are injected via getBuilder() below.
     *
     * 2. AppSession reuse across fresh logins (proprietary): when the
     *    backend has just linked an AppSession to this access token, the
     *    session UUID is pushed back to the client so the client can
     *    reclaim the same session on its next fresh login. Refresh flows
     *    are already handled natively by league/oauth2-server via the
     *    encrypted refresh_token payload (see RefreshTokenGrant::
     *    validateOldRefreshToken + Manager::getAppSession() first branch).
     *
     *    The client persists the UUID locally and re-sends it on the next
     *    /token request via the `session_id` POST parameter, where
     *    Manager::getAppSession() matches it against AppSessionRepository.
     *
     *    Historically this UUID was emitted only in the `id_token` field —
     *    a hack that conflicts with the OIDC meaning of that field (a JWT).
     *    Since OIDC support was introduced, we emit a clean `session_id`
     *    key in parallel. The legacy `id_token = <uuid>` branch is retained
     *    for non-OIDC scopes only, so that older mobile builds — which read
     *    `tokenResponse.idToken` and persist it as their session identifier
     *    (see espace-militant commit efadf40) — keep working during the
     *    rollout. Once every client reads `session_id`, drop the legacy
     *    assignment below.
     *
     *    The `??=` guard is critical: never overwrite an OIDC JWT id_token
     *    with the session UUID, which would silently break relying parties
     *    that expect a signed JWT in that field.
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        $params = $this->hasOpenIdScope($accessToken)
            ? parent::getExtraParams($accessToken)
            : [];

        if (null !== $accessToken->currentSessionUuid) {
            $sessionUuid = (string) $accessToken->currentSessionUuid;
            $params['session_id'] = $sessionUuid;
            $params['id_token'] ??= $sessionUuid;
        }

        return $params;
    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity): Builder
    {
        $expiresAt = $accessToken->getExpiryDateTime();
        if ($expiresAt instanceof \DateTime) {
            $expiresAt = \DateTimeImmutable::createFromMutable($expiresAt);
        }

        $builder = new Builder(new JoseEncoder(), ChainedFormatter::withUnixTimestampDates())
            ->permittedFor($accessToken->getClient()->getIdentifier())
            ->issuedBy($this->issuer)
            ->issuedAt(new \DateTimeImmutable())
            ->expiresAt($expiresAt)
            ->relatedTo($userEntity->getIdentifier())
        ;

        if (null !== $accessToken->nonce) {
            $builder = $builder->withClaim('nonce', $accessToken->nonce);
        }

        return $builder;
    }

    private function hasOpenIdScope(AccessTokenEntityInterface $accessToken): bool
    {
        return array_any($accessToken->getScopes(), fn ($scope) => Scope::OPENID === $scope->getIdentifier());
    }
}
