<?php

namespace Tests\AppBundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 * @group controller
 */
class ReactControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }

    public function testProjetsCitoyens()
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }
}
