<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\UserAuthorization;
use App\Entity\PostAddress;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UserAuthorizationTest extends TestCase
{
    public function testConstructor(): void
    {
        $authorization = $this->createUserAuthorization();
        self::assertInstanceOf(UuidInterface::class, $authorization->getUuid());
        self::assertNull($authorization->getId());
        self::assertSame('test_name', $authorization->getClientName());
        self::assertTrue($authorization->doesClientNeedUserAuthorization());
    }

    public function testSupportsScopes(): void
    {
        $authorization = $this->createUserAuthorization();
        $scopes = [Scope::READ_USERS()];

        static::assertFalse($authorization->supportsScopes($scopes));
        $authorization->setScopes($scopes);
        static::assertTrue($authorization->supportsScopes($scopes));
    }

    public function testBelongsTo(): void
    {
        $user = $this->createUser();
        $authorization = $this->createUserAuthorization($user);

        self::assertTrue($authorization->belongsTo($user));
        self::assertFalse($authorization->belongsTo($this->createUser()));
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Instance of App\OAuth\Model\Scope must be provided
     */
    public function testSupportsScopesExceptionWhenScopeTypeIsNotValid(): void
    {
        $this->createUserAuthorization()->supportsScopes(['public']);
    }

    private function createUserAuthorization(Adherent $user = null): UserAuthorization
    {
        $user = $user ?: $this->createUser();

        return new UserAuthorization(null, $user, new Client(null, 'test_name'));
    }

    public function createUser(): Adherent
    {
        return Adherent::create(
            Uuid::uuid4(),
            'fake@example.org',
            '',
            'male',
            'Fake',
            'Member',
            new \DateTime('-30 years'),
            '',
            PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024')
        );
    }
}
