<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group facebook
 */
class FacebookControllerTest extends WebTestCase
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
}
