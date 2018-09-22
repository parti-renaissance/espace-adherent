<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group controller
 */
class ReactControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testProjetsCitoyens()
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens');

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
