<?php

declare(strict_types=1);

namespace Tests\App\Entity\OAuth;

use App\Entity\OAuth\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

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
        self::assertInstanceOf(Uuid::class, $client->getUuid());
        self::assertTrue($client->isAskUserForAuthorization());
    }

    public function testSetAllowedGrantThrowExceptionsWhenInvalidDataAreGiven(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('"dummy" is not a valid grant type. Use constants defined in App\OAuth\Model\GrantTypeEnum.');
        $client = $this->createClient();
        $client->setAllowedGrantTypes(['dummy']);
    }

    public function testPkceRequiredDefaultsToFalse(): void
    {
        $client = $this->createClient();
        self::assertFalse($client->isPkceRequired());
    }

    public function testPkceRequiredCanBeSetTrue(): void
    {
        $client = $this->createClient();
        $client->setPkceRequired(true);
        self::assertTrue($client->isPkceRequired());
    }

    public function testPostLogoutRedirectUrisDefaultsToEmptyArray(): void
    {
        $client = $this->createClient();

        self::assertSame([], $client->getPostLogoutRedirectUris());
        self::assertFalse($client->hasPostLogoutRedirectUri('https://example.com/logout'));
    }

    public function testPostLogoutRedirectUrisCanBeSet(): void
    {
        $client = $this->createClient();

        $client->setPostLogoutRedirectUris(['https://a.example.com/logout', 'https://b.example.com/logout']);

        self::assertSame(['https://a.example.com/logout', 'https://b.example.com/logout'], $client->getPostLogoutRedirectUris());
    }

    public function testHasPostLogoutRedirectUriIsStrictEquality(): void
    {
        $client = $this->createClient();
        $client->setPostLogoutRedirectUris(['https://example.com/logout']);

        self::assertTrue($client->hasPostLogoutRedirectUri('https://example.com/logout'));
        self::assertFalse($client->hasPostLogoutRedirectUri('https://example.com/logout/'));
        self::assertFalse($client->hasPostLogoutRedirectUri('https://EXAMPLE.com/logout'));
    }

    public function testRedirectUrisCanBeAddedAndRemoved(): void
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
