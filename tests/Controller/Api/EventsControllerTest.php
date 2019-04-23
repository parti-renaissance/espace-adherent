<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\Entity\EventCategory;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ApiControllerTestTrait;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class EventsControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testApiUpcomingEvents()
    {
        $this->client->request(Request::METHOD_GET, '/api/events');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertGreaterThanOrEqual(7, \count(\GuzzleHttp\json_decode($content, true)));
        $this->assertEachJsonItemContainsKey('uuid', $content);
        $this->assertEachJsonItemContainsKey('slug', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('position', $content);
        $this->assertEachJsonItemContainsKey('committee_name', $content, 0);
        $this->assertEachJsonItemContainsKey('committee_url', $content, 0);
    }

    /**
     * @dataProvider provideApiEventsCategories
     */
    public function testApiUpcomingEventsForCategory(string $categoryCode, int $expectedCount)
    {
        $categoryName = LoadEventCategoryData::LEGACY_EVENT_CATEGORIES[$categoryCode];
        $category = $this->getRepository(EventCategory::class)->findOneBy(['name' => $categoryName]);

        $this->client->request(Request::METHOD_GET, '/api/events?type='.$category->getId());

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
        $this->assertEachJsonItemContainsKey('committee_name', $content);
        $this->assertEachJsonItemContainsKey('committee_url', $content);
    }

    public function provideApiEventsCategories()
    {
        return [
            ['CE011', 0],
            ['CE001', 1],
            ['CE005', 1],
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->init();
    }

    public function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
