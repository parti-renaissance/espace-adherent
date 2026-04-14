<?php

declare(strict_types=1);

namespace Tests\App\OAuth\Oidc;

use App\OAuth\Oidc\JwkSetProvider;
use League\OAuth2\Server\CryptKey;
use PHPUnit\Framework\TestCase;

class JwkSetProviderTest extends TestCase
{
    private string $publicKeyPath;
    private JwkSetProvider $provider;

    protected function setUp(): void
    {
        $privateKeyResource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => \OPENSSL_KEYTYPE_RSA,
        ]);
        $details = openssl_pkey_get_details($privateKeyResource);

        $this->publicKeyPath = tempnam(sys_get_temp_dir(), 'oidc_pub_');
        file_put_contents($this->publicKeyPath, $details['key']);
        chmod($this->publicKeyPath, 0o600);

        $this->provider = new JwkSetProvider(new CryptKey($this->publicKeyPath, null, false));
    }

    protected function tearDown(): void
    {
        if (file_exists($this->publicKeyPath)) {
            unlink($this->publicKeyPath);
        }
    }

    public function testGetJwkSetHasOneKey(): void
    {
        $jwks = $this->provider->getJwkSet();

        self::assertArrayHasKey('keys', $jwks);
        self::assertCount(1, $jwks['keys']);
    }

    public function testJwkFormatIsRsaSig(): void
    {
        $jwk = $this->provider->getJwkSet()['keys'][0];

        self::assertSame('RSA', $jwk['kty']);
        self::assertSame('sig', $jwk['use']);
        self::assertSame('RS256', $jwk['alg']);
    }

    public function testJwkContainsNAndE(): void
    {
        $jwk = $this->provider->getJwkSet()['keys'][0];

        self::assertArrayHasKey('n', $jwk);
        self::assertArrayHasKey('e', $jwk);
        self::assertNotEmpty($jwk['n']);
        self::assertNotEmpty($jwk['e']);
    }

    public function testJwkNAndEAreBase64Url(): void
    {
        $jwk = $this->provider->getJwkSet()['keys'][0];

        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $jwk['n']);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $jwk['e']);
    }

    public function testKidIsDeterministic(): void
    {
        $kid1 = $this->provider->getKid();
        $kid2 = $this->provider->getKid();

        self::assertSame($kid1, $kid2);
    }

    public function testKidIsHex16Chars(): void
    {
        $kid = $this->provider->getKid();

        self::assertSame(16, \strlen($kid));
        self::assertMatchesRegularExpression('/^[a-f0-9]{16}$/', $kid);
    }

    public function testKidIsSameInJwkAndInGetKid(): void
    {
        self::assertSame($this->provider->getKid(), $this->provider->getJwkSet()['keys'][0]['kid']);
    }
}
