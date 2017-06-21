<?php

namespace Tests\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class StatsControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/api/stats');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        $data = \GuzzleHttp\json_decode($content, true);
        $this->assertArrayHasKey('userCount', $data);
        $this->assertArrayHasKey('eventCount', $data);
        $this->assertArrayHasKey('committeeCount', $data);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
