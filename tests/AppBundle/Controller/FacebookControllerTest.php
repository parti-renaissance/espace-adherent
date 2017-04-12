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
        $crawler = $this->client->request(Request::METHOD_GET, '/facebook');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    public function testAuth()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/facebook/auth');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
    }

    public function testUserIdWithCode()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/facebook/auth?code=code');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
    }

    public function testUserIdWithoutCode()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/facebook/auth');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
    }

    public function testProcessPictureNotFoundAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/facebook/process/unknown_id');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $response = $this->client->getResponse());
    }

    public function testProcessPictureAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/facebook/process/facebook');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());

        $this->assertEquals(5, $crawler->filter('a.facebook-download')->count());
        $this->assertEquals(5, $crawler->filter('img.facebook-picture')->count());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->client = $this->makeClient();
    }
}
