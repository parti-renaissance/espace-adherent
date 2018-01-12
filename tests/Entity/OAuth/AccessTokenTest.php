<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\OAuth\AccessToken;
use AppBundle\Entity\OAuth\Client;
use AppBundle\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group time-sensitive
 */
class AccessTokenTest extends TestCase
{
    private const IDENTIFIER = 'ccc1a6a661c35cce8a185a413e4b8c3dd7c9b655cf35e3400d4b571484a6c3f1ab0cd92140ea9978';
    private const UUID = '2e1de7cd-5a54-46f9-ae18-0993602a78a5';

    public function testCreateAccessToken(): void
    {
        $accessToken = $this->createAccessToken('+5 hours');

        static::assertSame(self::IDENTIFIER, $accessToken->getIdentifier());
        static::assertEquals(Uuid::fromString(self::UUID), $accessToken->getUuid());
        static::assertInstanceOf(\DateTimeImmutable::class, $accessToken->getExpiryDateTime());
        static::assertSame(strtotime('+5 hours'), (int) $accessToken->getExpiryDateTime()->format('U'));
        static::assertSame(['public', 'user_profile', 'manage_committees'], $accessToken->getScopes());
        static::assertInstanceOf(Adherent::class, $accessToken->getUser());
        static::assertInstanceOf(Client::class, $accessToken->getClient());
        static::assertFalse($accessToken->isExpired());
        static::assertFalse($accessToken->isRevoked());
    }

    public function testRevokeToken(): void
    {
        $accessToken = $this->createAccessToken('+10 hours');

        static::assertFalse($accessToken->isRevoked());

        $accessToken->revoke();

        static::assertTrue($accessToken->isRevoked());
    }

    public function testExpiredToken(): void
    {
        $accessToken = $this->createAccessToken('-2 hours');

        static::assertTrue($accessToken->isExpired());
    }

    private function createAccessToken(string $expiryDateTime): AccessToken
    {
        $token = new AccessToken(
            Uuid::fromString(self::UUID),
            $this->createMock(Adherent::class),
            self::IDENTIFIER,
            \DateTime::createFromFormat('U', strtotime($expiryDateTime)),
            $this->createMock(Client::class)
        );
        $token->addScope('public');
        $token->addScopes(['user_profile', 'manage_committees']);

        return $token;
    }
}
