<?php

declare(strict_types=1);

namespace App\OAuth\Oidc;

use App\OAuth\Model\Scope;
use OpenIDConnectServer\ClaimExtractor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OidcDiscoveryMetadataBuilder
{
    private const SCOPES_SUPPORTED = [
        Scope::OPENID,
        Scope::PROFILE,
        Scope::EMAIL,
        Scope::OFFLINE_ACCESS,
    ];

    private const REGISTERED_CLAIMS = ['sub', 'iss', 'aud', 'iat', 'exp', 'nonce'];

    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly ClaimExtractor $claimExtractor,
        private readonly string $issuer,
    ) {
    }

    public function build(): array
    {
        return [
            'issuer' => $this->issuer,
            'authorization_endpoint' => $this->router->generate('app_front_oauth_authorize', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'token_endpoint' => $this->router->generate('app_front_oauth_get_access_token', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'userinfo_endpoint' => $this->router->generate('app_oidc_userinfo', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'jwks_uri' => $this->router->generate('app_oidc_jwks', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'end_session_endpoint' => $this->router->generate('app_oidc_end_session', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => self::SCOPES_SUPPORTED,
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post'],
            'code_challenge_methods_supported' => ['S256'],
            'claim_types_supported' => ['normal'],
            'claims_parameter_supported' => false,
            'request_parameter_supported' => false,
            'request_uri_parameter_supported' => false,
            'require_request_uri_registration' => false,
            'frontchannel_logout_supported' => false,
            'backchannel_logout_supported' => false,
            'claims_supported' => $this->computeClaimsSupported(),
        ];
    }

    private function computeClaimsSupported(): array
    {
        $claims = self::REGISTERED_CLAIMS;

        foreach (self::SCOPES_SUPPORTED as $scope) {
            if (null !== $claimSet = $this->claimExtractor->getClaimSet($scope)) {
                $claims = array_merge($claims, $claimSet->getClaims());
            }
        }

        return array_values(array_unique($claims));
    }
}
