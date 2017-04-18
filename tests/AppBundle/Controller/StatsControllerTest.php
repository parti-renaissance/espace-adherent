<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functionnal
 */
class StatsControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/api/stats');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertJson($this->client->getResponse()->getContent());
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
