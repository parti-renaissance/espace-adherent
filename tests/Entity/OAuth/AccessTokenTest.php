<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
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
        $accessToken = $this->createAccessToken(time() + 5 * 3600);

        static::assertSame(self::IDENTIFIER, $accessToken->getIdentifier());
        static::assertEquals(Uuid::fromString(self::UUID), $accessToken->getUuid());
        static::assertInstanceOf(\DateTimeImmutable::class, $accessToken->getExpiryDateTime());
        static::assertSame(time() + 5 * 3600, (int) $accessToken->getExpiryDateTime()->format('U'));
        static::assertSame(['public', 'user_profile', 'manage_committees'], $accessToken->getScopes());
        static::assertInstanceOf(Adherent::class, $accessToken->getUser());
        static::assertInstanceOf(Client::class, $accessToken->getClient());
        static::assertFalse($accessToken->isExpired());
        static::assertFalse($accessToken->isRevoked());
    }

    public function testRevokeToken(): void
    {
        $accessToken = $this->createAccessToken(time() + 36000);

        static::assertFalse($accessToken->isRevoked());

        $accessToken->revoke();

        static::assertTrue($accessToken->isRevoked());
    }

    public function testExpiredToken(): void
    {
        $accessToken = $this->createAccessToken(time() - 2 * 3600);

        static::assertTrue($accessToken->isExpired());
    }

    private function createAccessToken(int $time): AccessToken
    {
        $token = new AccessToken(
            Uuid::fromString(self::UUID),
            $this->createMock(Adherent::class),
            self::IDENTIFIER,
            \DateTime::createFromFormat('U', $time),
            $this->createMock(Client::class)
        );
        $token->addScope('public');
        $token->addScopes(['user_profile', 'manage_committees']);

        return $token;
    }
}
