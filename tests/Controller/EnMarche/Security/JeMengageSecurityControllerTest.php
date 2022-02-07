<?php

namespace Tests\App\Controller\EnMarche\Security;

use App\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group security
 */
class JeMengageSecurityControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testAdherentWithoutRoleCannotConnectToJeMengageApp(): void
    {
        $this->client->request(Request::METHOD_GET, 'http://login.jemengage.code/oauth/v2/auth?response_type=code&client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'carl999@example.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://login.jemengage.code/oauth/v2/auth?client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c&response_type=code', $this->client);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testAdherentWithGoodRoleCanConnectToJeMengageApp(): void
    {
        $this->client->request(Request::METHOD_GET, 'http://login.jemengage.code/oauth/v2/auth?response_type=code&client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c&scope=jemengage_admin');
        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'referent@en-marche-dev.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://login.jemengage.code/oauth/v2/auth?client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c&response_type=code&scope=jemengage_admin', $this->client);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
        $this->assertStringStartsWith('http://localhost:3000/auth?code=', $response->headers->get('location'));
    }
}
