<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\MysqlWebTestCase;

class SearchControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    /**
     * @group functionnal
     */
    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/recherche');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
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
