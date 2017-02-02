<?php

namespace Tests\AppBundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/search');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->client = null;

        parent::tearDown();
    }
}
