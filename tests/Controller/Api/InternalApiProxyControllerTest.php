<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadInternalApiApplicationData;
use App\Scope\ScopeEnum;
use League\OAuth2\Server\CryptKey;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class InternalApiProxyControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    /** @var CryptKey */
    private $privateCryptKey;

    public function testGetEventsReturnValidJsonResponse(): void
    {
        $accessToken = $this->getJwtAccessTokenByIdentifier('l9efhked975s1z1og3z10anp8ydi6tkmha468906g1tu0hb5hltni7xvsuipg5n7tkslzqjttyfn69cd', $this->privateCryptKey);

        $url = \sprintf('/api/v3/internal/%s/api/v3/events', LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_03_UUID);

        $this->client->request(Request::METHOD_GET, $url, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('items', $data);
        self::assertCount(2, $data['items']);
    }

    public function testGetEventsWithScopeReturnValidJsonResponse(): void
    {
        $accessToken = $this->getJwtAccessTokenByIdentifier('l9efhked975s1z1og3z10anp8ydi6tkmha468906g1tu0hb5hltni7xvsuipg5n7tkslzqjttyfn69cd', $this->privateCryptKey);

        $url = \sprintf(
            '/api/v3/internal/%s/api/v3/events?scope=%s',
            LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_04_UUID,
            ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY
        );

        $this->client->request(Request::METHOD_GET, $url, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('items', $data);
        self::assertCount(2, $data['items']);
    }

    public function testGetEventsWithExtraParametersReturnValidJsonResponse(): void
    {
        $accessToken = $this->getJwtAccessTokenByIdentifier('l9efhked975s1z1og3z10anp8ydi6tkmha468906g1tu0hb5hltni7xvsuipg5n7tkslzqjttyfn69cd', $this->privateCryptKey);

        $url = \sprintf(
            '/api/v3/internal/%s/api/v3/events?scope=%s&name=%s',
            LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_04_UUID,
            ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
            urlencode('Grand Meeting'),
        );

        $this->client->request(Request::METHOD_GET, $url, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('items', $data);
        self::assertCount(1, $data['items']);
    }

    public function testGetEventsWithScopeThatUserDoesNotHave(): void
    {
        $accessToken = $this->getJwtAccessTokenByIdentifier('l9efhked975s1z1og3z10anp8ydi6tkmha468906g1tu0hb5hltni7xvsuipg5n7tkslzqjttyfn69cd', $this->privateCryptKey);

        $url = \sprintf(
            '/api/v3/internal/%s/api/v3/events?scope=%s',
            LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_04_UUID,
            ScopeEnum::CANDIDATE
        );

        $this->client->request(Request::METHOD_GET, $url, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testGetEventsWithWrongScope(): void
    {
        $accessToken = $this->getJwtAccessTokenByIdentifier('l9efhked975s1z1og3z10anp8ydi6tkmha468906g1tu0hb5hltni7xvsuipg5n7tkslzqjttyfn69cd', $this->privateCryptKey);

        $url = \sprintf(
            '/api/v3/internal/%s/api/v3/events?scope=invalid',
            LoadInternalApiApplicationData::INTERNAL_API_APPLICATION_04_UUID,
        );

        $this->client->request(Request::METHOD_GET, $url, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->privateCryptKey = new CryptKey($this->getParameter('ssl_private_key'), null, false);
    }

    protected function tearDown(): void
    {
        $this->privateCryptKey = null;

        parent::tearDown();
    }
}
