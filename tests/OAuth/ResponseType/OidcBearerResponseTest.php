<?php

declare(strict_types=1);

namespace Tests\App\OAuth\ResponseType;

use App\Entity\Adherent;
use App\OAuth\Model\AccessToken;
use App\OAuth\Model\Scope;
use App\OAuth\Oidc\JwkSetProvider;
use App\OAuth\ResponseType\OidcBearerResponse;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class OidcBearerResponseTest extends TestCase
{
    private const ISSUER = 'https://user-vox.example.com';
    private const KID = 'test-kid';

    private string $privateKeyPath;
    private string $publicKeyPath;

    protected function setUp(): void
    {
        $privateResource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => \OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($privateResource, $privateKeyPem);
        $details = openssl_pkey_get_details($privateResource);

        $this->privateKeyPath = tempnam(sys_get_temp_dir(), 'oidc_priv_');
        file_put_contents($this->privateKeyPath, $privateKeyPem);
        chmod($this->privateKeyPath, 0o600);

        $this->publicKeyPath = tempnam(sys_get_temp_dir(), 'oidc_pub_');
        file_put_contents($this->publicKeyPath, $details['key']);
        chmod($this->publicKeyPath, 0o600);
    }

    protected function tearDown(): void
    {
        foreach ([$this->privateKeyPath, $this->publicKeyPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function testReturnsSessionUuidInBothLegacyAndCleanFieldsWhenNoOpenidScope(): void
    {
        $accessToken = $this->createAccessToken([Scope::WRITE_PROFILE]);
        $accessToken->currentSessionUuid = Uuid::uuid4();

        $response = $this->createResponse($this->identityProviderNeverCalled());

        $params = $this->invokeGetExtraParams($response, $accessToken);

        $uuid = (string) $accessToken->currentSessionUuid;
        self::assertSame(['session_id' => $uuid, 'id_token' => $uuid], $params);
    }

    public function testReturnsEmptyWhenNoOpenidScopeAndNoSessionUuid(): void
    {
        $accessToken = $this->createAccessToken([Scope::WRITE_PROFILE]);

        $response = $this->createResponse($this->identityProviderNeverCalled());

        self::assertSame([], $this->invokeGetExtraParams($response, $accessToken));
    }

    public function testOpenidScopeWithSessionUuidEmitsJwtAndSessionIdWithoutOverwritingJwt(): void
    {
        $accessToken = $this->createAccessToken([Scope::OPENID]);
        $accessToken->currentSessionUuid = Uuid::uuid4();

        $response = $this->createResponse($this->identityProviderReturning($this->createAdherent()));

        $params = $this->invokeGetExtraParams($response, $accessToken);

        self::assertArrayHasKey('id_token', $params);
        self::assertArrayHasKey('session_id', $params);
        self::assertSame((string) $accessToken->currentSessionUuid, $params['session_id']);
        // id_token stays the OIDC JWT — NOT the session UUID
        self::assertNotSame((string) $accessToken->currentSessionUuid, $params['id_token']);
        self::assertInstanceOf(Plain::class, $this->parse($params['id_token']));
    }

    public function testReturnsSignedJwtWhenOpenidScopePresent(): void
    {
        $accessToken = $this->createAccessToken([Scope::OPENID, Scope::EMAIL]);

        $response = $this->createResponse($this->identityProviderReturning($this->createAdherent()));

        $params = $this->invokeGetExtraParams($response, $accessToken);

        self::assertArrayHasKey('id_token', $params);

        $token = $this->parse($params['id_token']);

        self::assertSame(self::ISSUER, $token->claims()->get('iss'));
        self::assertSame([$accessToken->getClient()->getIdentifier()], $token->claims()->get('aud'));
        self::assertSame(self::KID, $token->headers()->get('kid'));
        self::assertSame('john.doe@example.com', $token->claims()->get('email'));
        self::assertTrue(new Validator()->validate(
            $token,
            new SignedWith(new Sha256(), InMemory::file($this->publicKeyPath)),
        ));
    }

    public function testJwtIncludesNonceWhenPresent(): void
    {
        $accessToken = $this->createAccessToken([Scope::OPENID]);
        $accessToken->nonce = 'test-nonce-xyz';

        $response = $this->createResponse($this->identityProviderReturning($this->createAdherent()));

        $params = $this->invokeGetExtraParams($response, $accessToken);

        self::assertSame('test-nonce-xyz', $this->parse($params['id_token'])->claims()->get('nonce'));
    }

    public function testJwtOmitsNonceWhenAbsent(): void
    {
        $accessToken = $this->createAccessToken([Scope::OPENID]);

        $response = $this->createResponse($this->identityProviderReturning($this->createAdherent()));

        $params = $this->invokeGetExtraParams($response, $accessToken);

        self::assertFalse($this->parse($params['id_token'])->claims()->has('nonce'));
    }

    private function createResponse(IdentityProviderInterface $identityProvider): OidcBearerResponse
    {
        $jwkSetProvider = $this->createStub(JwkSetProvider::class);
        $jwkSetProvider->method('getKid')->willReturn(self::KID);

        $response = new OidcBearerResponse(
            $identityProvider,
            new ClaimExtractor(),
            $jwkSetProvider,
            self::ISSUER,
        );

        $response->setPrivateKey(new CryptKey($this->privateKeyPath, null, false));
        $response->setEncryptionKey(base64_encode(random_bytes(32)));

        return $response;
    }

    /**
     * @param string[] $scopeIdentifiers
     */
    private function createAccessToken(array $scopeIdentifiers): AccessToken
    {
        $accessToken = new AccessToken();
        $accessToken->setIdentifier(Uuid::uuid4()->toString());

        $client = $this->createStub(ClientEntityInterface::class);
        $client->method('getIdentifier')->willReturn(Uuid::uuid4()->toString());
        $accessToken->setClient($client);

        $accessToken->setUserIdentifier(Uuid::uuid4()->toString());
        $accessToken->setExpiryDateTime(new \DateTimeImmutable('+1 hour'));

        foreach ($scopeIdentifiers as $identifier) {
            $scope = $this->createStub(ScopeEntityInterface::class);
            $scope->method('getIdentifier')->willReturn($identifier);
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    private function createAdherent(): Adherent
    {
        $adherent = new Adherent();
        $reflection = new \ReflectionClass($adherent);
        $reflection->getProperty('uuid')->setValue($adherent, Uuid::uuid4());
        $reflection->getProperty('emailAddress')->setValue($adherent, 'john.doe@example.com');
        $reflection->getProperty('firstName')->setValue($adherent, 'John');
        $reflection->getProperty('lastName')->setValue($adherent, 'Doe');
        $reflection->getProperty('nickname')->setValue($adherent, 'johnny');
        $reflection->getProperty('activatedAt')->setValue($adherent, new \DateTime('2026-01-15'));
        $reflection->getProperty('updatedAt')->setValue($adherent, new \DateTime('2026-04-10'));

        return $adherent;
    }

    private function identityProviderReturning(Adherent $adherent): IdentityProviderInterface
    {
        $provider = $this->createStub(IdentityProviderInterface::class);
        $provider->method('getUserEntityByIdentifier')->willReturn($adherent);

        return $provider;
    }

    private function identityProviderNeverCalled(): IdentityProviderInterface
    {
        $provider = $this->createMock(IdentityProviderInterface::class);
        $provider->expects(self::never())->method('getUserEntityByIdentifier');

        return $provider;
    }

    private function parse(string $jwt): Plain
    {
        $parsed = new Parser(new JoseEncoder())->parse($jwt);
        self::assertInstanceOf(Plain::class, $parsed);

        return $parsed;
    }

    private function invokeGetExtraParams(OidcBearerResponse $response, AccessTokenEntityInterface $accessToken): array
    {
        return new \ReflectionMethod($response, 'getExtraParams')->invoke($response, $accessToken);
    }
}
