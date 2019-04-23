<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\OAuth\AuthorizationCode;
use AppBundle\Entity\OAuth\Client;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AuthorizationCodeTest extends TestCase
{
    private const IDENTIFIER = '3d444653008c04f7fa754f754c3029b7ba728345fece415ae9d2b9e67dd123b68b6c9fdd97ea0468';
    private const UUID = '65a1c877-9cf9-4e61-99a2-b8ae7a6ae6ea';

    public function testCreateAccessToken(): void
    {
        $authCode = $this->createAuthorizationCode('+5 hours');

        $this->assertSame(self::IDENTIFIER, $authCode->getIdentifier());
        $this->assertEquals(Uuid::fromString(self::UUID), $authCode->getUuid());
        $this->assertSame('https://my.app.com/oauth/callback', $authCode->getRedirectUri());
        $this->assertInstanceOf(\DateTimeImmutable::class, $authCode->getExpiryDateTime());
        $this->assertEquals((new \DateTime('+5 hours'))->format('U'), $authCode->getExpiryDateTime()->format('U'));
        $this->assertSame(['public', 'user_profile', 'manage_committees'], $authCode->getScopes());
        $this->assertInstanceOf(Adherent::class, $authCode->getUser());
        $this->assertInstanceOf(Client::class, $authCode->getClient());
        $this->assertFalse($authCode->isExpired());
        $this->assertFalse($authCode->isRevoked());
    }

    public function testRevokeToken(): void
    {
        $authCode = $this->createAuthorizationCode('+10 hours');

        $this->assertFalse($authCode->isRevoked());

        $authCode->revoke();

        $this->assertTrue($authCode->isRevoked());
    }

    public function testExpiredToken(): void
    {
        $authCode = $this->createAuthorizationCode('-2 hours');

        $this->assertTrue($authCode->isExpired());
    }

    private function createAuthorizationCode(string $expiryDateTime): AuthorizationCode
    {
        $token = new AuthorizationCode(
            Uuid::fromString(self::UUID),
            $this->createMock(Adherent::class),
            self::IDENTIFIER,
            new \DateTime($expiryDateTime),
            'https://my.app.com/oauth/callback',
            $this->createMock(Client::class)
        );
        $token->addScope('public');
        $token->addScopes(['user_profile', 'manage_committees']);

        return $token;
    }
}
