<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functionnal
 */
class CommitteesControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testGetApiEventsWithInvalidType()
    {
        $this->client->request(Request::METHOD_GET, '/api/events?type=FOO');

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    /**
     * @dataProvider provideApiUrl
     */
    public function testGetApiEndpointAction(string $url)
    {
        $this->client->request(Request::METHOD_GET, $url);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function provideApiUrl()
    {
        return [
            ['/api/committees'],
            ['/api/events'],
            ['/api/events?type=CE001'],
            ['/api/events?type=CE002'],
            ['/api/events?type=CE003'],
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventData::class,
        ]);
    }

    public function tearDown()
    {
        $this->kill([]);

        parent::tearDown();
    }
}
