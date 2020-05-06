<?php

namespace Tests\App\Entity;

use App\Entity\OAuth\Client;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ClientTest extends TestCase
{
    public function testCreateClient(): void
    {
        $client = $this->createClient();
        self::assertEmpty($client->getName());
        self::assertEmpty($client->getId());
        self::assertEmpty($client->getAllowedGrantTypes());
        self::assertEmpty($client->getDescription());
        self::assertEmpty($client->getRedirectUris());
        self::assertSame(44, mb_strlen($client->getSecret()));
        self::assertEmpty($client->getDeletedAt());
        self::assertInstanceOf(UuidInterface::class, $client->getUuid());
        self::assertTrue($client->isAskUserForAuthorization());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage "dummy" is not a valid grant type. Use constants defined in App\OAuth\Model\GrantTypeEnum.
     */
    public function testSetAllowedGrantThrowExceptionsWhenInvalidDataAreGiven(): void
    {
        $client = $this->createClient();
        $client->setAllowedGrantTypes(['dummy']);
    }

    public function testRedirectUrisCanBeAddedAndRemoved()
    {
        $client = $this->createClient();
        $client->addRedirectUri('a');
        $client->addRedirectUri('b');
        $client->addRedirectUri('c');

        self::assertTrue($client->hasRedirectUri('a'));
        self::assertTrue($client->hasRedirectUri('b'));
        self::assertTrue($client->hasRedirectUri('c'));
        self::assertFalse($client->hasRedirectUri('d'));

        self::assertSame(['a', 'b', 'c'], $client->getRedirectUris());

        $client->removeRedirectUri('b');
        self::assertSame([0 => 'a', 1 => 'c'], $client->getRedirectUris());
        self::assertFalse($client->hasRedirectUri('b'));
    }

    public function createClient(): Client
    {
        return new Client();
    }
}
