<?php

declare(strict_types=1);

namespace Tests\App\OAuth\Oidc;

use App\OAuth\Model\Scope;
use App\OAuth\Oidc\OidcDiscoveryMetadataBuilder;
use OpenIDConnectServer\ClaimExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OidcDiscoveryMetadataBuilderTest extends TestCase
{
    private const ISSUER = 'https://user-vox.example.com';

    private OidcDiscoveryMetadataBuilder $builder;

    protected function setUp(): void
    {
        $router = $this->createStub(UrlGeneratorInterface::class);
        $router
            ->method('generate')
            ->willReturnCallback(static function (string $name) {
                return match ($name) {
                    'app_front_oauth_authorize' => 'https://user-vox.example.com/oauth/v2/auth',
                    'app_front_oauth_get_access_token' => 'https://user-vox.example.com/oauth/v2/token',
                    'app_oidc_userinfo' => 'https://user-vox.example.com/oauth/v2/userinfo',
                    'app_oidc_jwks' => 'https://user-vox.example.com/oauth/v2/jwks',
                    'app_oidc_end_session' => 'https://user-vox.example.com/oauth/v2/end-session',
                    default => throw new \InvalidArgumentException("Unexpected route: $name"),
                };
            })
        ;

        $this->builder = new OidcDiscoveryMetadataBuilder($router, new ClaimExtractor(), self::ISSUER);
    }

    public function testBuildClaimsSupportedIsDerivedFromClaimExtractor(): void
    {
        $claims = $this->builder->build()['claims_supported'];

        foreach (['sub', 'iss', 'aud', 'iat', 'exp', 'nonce'] as $registered) {
            self::assertContains($registered, $claims);
        }

        foreach (['email', 'email_verified'] as $emailClaim) {
            self::assertContains($emailClaim, $claims);
        }

        foreach (['name', 'given_name', 'family_name', 'preferred_username', 'updated_at'] as $profileClaim) {
            self::assertContains($profileClaim, $claims);
        }
    }

    public function testBuildContainsIssuer(): void
    {
        self::assertSame(self::ISSUER, $this->builder->build()['issuer']);
    }

    public function testBuildContainsAllRequiredOidcFields(): void
    {
        $metadata = $this->builder->build();

        $required = [
            'issuer',
            'authorization_endpoint',
            'token_endpoint',
            'userinfo_endpoint',
            'jwks_uri',
            'response_types_supported',
            'grant_types_supported',
            'subject_types_supported',
            'id_token_signing_alg_values_supported',
            'scopes_supported',
            'token_endpoint_auth_methods_supported',
            'code_challenge_methods_supported',
        ];

        foreach ($required as $key) {
            self::assertArrayHasKey($key, $metadata, "Missing required OIDC metadata key: $key");
        }
    }

    public function testBuildScopesSupportedContainsOidcScopes(): void
    {
        $scopes = $this->builder->build()['scopes_supported'];

        self::assertContains(Scope::OPENID, $scopes);
        self::assertContains(Scope::PROFILE, $scopes);
        self::assertContains(Scope::EMAIL, $scopes);
        self::assertContains(Scope::OFFLINE_ACCESS, $scopes);
    }

    public function testBuildIdTokenSigningAlgIsRs256(): void
    {
        self::assertSame(['RS256'], $this->builder->build()['id_token_signing_alg_values_supported']);
    }

    public function testBuildUsesRouterForEndpoints(): void
    {
        $metadata = $this->builder->build();

        self::assertSame('https://user-vox.example.com/oauth/v2/auth', $metadata['authorization_endpoint']);
        self::assertSame('https://user-vox.example.com/oauth/v2/token', $metadata['token_endpoint']);
        self::assertSame('https://user-vox.example.com/oauth/v2/userinfo', $metadata['userinfo_endpoint']);
        self::assertSame('https://user-vox.example.com/oauth/v2/jwks', $metadata['jwks_uri']);
    }
}
