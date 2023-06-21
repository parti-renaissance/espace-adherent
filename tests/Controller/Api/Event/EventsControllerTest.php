<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Entity\Event\EventCategory;
use App\OAuth\Model\GrantTypeEnum;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class EventsControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testApiUpcomingEvents()
    {
        Chronos::setTestNow('2018-05-18');

        $this->client->request(Request::METHOD_GET, '/api/upcoming-events');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertGreaterThanOrEqual(7, \count(json_decode($content, true)));
        $this->assertEachJsonItemContainsKey('uuid', $content);
        $this->assertEachJsonItemContainsKey('slug', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('position', $content);
        $this->assertEachJsonItemContainsKey('committee_name', $content, [3, 8]);
        $this->assertEachJsonItemContainsKey('committee_url', $content, [3, 8]);

        Chronos::setTestNow();
    }

    #[DataProvider('provideApiEventsCategories')]
    public function testApiUpcomingEventsForCategory(string $categoryCode, int $expectedCount, array $exclude = [])
    {
        Chronos::setTestNow('2018-05-18');

        $categoryName = LoadEventCategoryData::LEGACY_EVENT_CATEGORIES[$categoryCode];
        $category = $this->getRepository(EventCategory::class)->findOneBy(['name' => $categoryName]);

        $this->client->request(Request::METHOD_GET, '/api/upcoming-events?type='.$category->getId());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertCount($expectedCount, \GuzzleHttp\json_decode($content, true));
        $this->assertEachJsonItemContainsKey('uuid', $content);
        $this->assertEachJsonItemContainsKey('slug', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('position', $content);
        $this->assertEachJsonItemContainsKey('committee_name', $content, $exclude);
        $this->assertEachJsonItemContainsKey('committee_url', $content, $exclude);

        Chronos::setTestNow();
    }

    public function testICanRequestMySubscribedEvents()
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_11_UUID,
            'Ca1#79T6s^kCxqLc9sp$WbtqdOOsdf1iQ',
            GrantTypeEnum::PASSWORD,
            null,
            'gisele-berthoux@caramail.com',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/events?subscribedOnly', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame(5, $response['metadata']['total_items']);
        self::assertSame('5b279c9f-2b1e-4b93-9c34-1669f56e9d64', $response['items'][0]['uuid']);
    }

    public static function provideApiEventsCategories(): array
    {
        return [
            ['CE011', 0],
            ['CE001', 1, [1]],
            ['CE005', 1],
        ];
    }
}
