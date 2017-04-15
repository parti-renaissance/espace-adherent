<?php

namespace Tests\AppBundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functionnal
 */
class FacebookControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/facebook');
        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    public function testAuth()
    {
        $this->client->request(Request::METHOD_GET, '/facebook/auth');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
    }

    public function testUserIdWithCode()
    {
        $this->client->request(Request::METHOD_GET, '/facebook/auth?code=code');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->client = $this->makeClient();
    }
}
