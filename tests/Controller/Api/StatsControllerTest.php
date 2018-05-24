<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\DataFixtures\ORM\LoadUserData;
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

        $this->assertArraySubset([
            'userCount' => 19,
            'eventCount' => 19,
            'committeeCount' => 9,
        ], $data);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadUserData::class,
            LoadEventData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
