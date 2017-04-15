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
        $this->client->request(Request::METHOD_GET, '/profil-facebook');
        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    public function testAuth()
    {
        $this->client->request(Request::METHOD_GET, '/profil-facebook/connexion');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->client = $this->makeClient();
    }
}
