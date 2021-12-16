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
class CoalitionsSecurityControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testLoggedAsAdherentIWillBeRedirectedToFrontAppSuccessfully(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'http://login.coalitions.code');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'carl999@example.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://coalitions.code', $this->client);
    }

    public function testLoggedAsCoalitionUserIWillBeRedirectedToFrontAppSuccessfully(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'http://login.coalitions.code');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'coalitions-user-1@en-marche-dev.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://coalitions.code', $this->client);
    }

    public function testOnOAuthContextLoggedAsCoalitionUserIWillBeRedirectedToFrontAppSuccessfully(): void
    {
        $this->client->request(Request::METHOD_GET, $authUrl = 'http://login.coalitions.code/oauth/v2/auth?client_id=138140b3-1dd2-11b2-ad7e-2348ad4fef66&response_type=code');
        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'referent@en-marche-dev.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($authUrl, $this->client);

        $this->client->followRedirect();

        $this->client->submitForm('Accepter');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
        $this->assertStringStartsWith('http://client-oauth.docker:8000/client/receive_authcode?code=', $response->headers->get('location'));
    }

    public function testJeMengageUserCannotLogin(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'http://login.coalitions.code');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'je-mengage-user-1@en-marche-dev.fr',
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/', $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertCount(1, $errors = $crawler->filter('#auth-error'));
        $this->assertSame('L\'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas.', $errors->text());
    }
}
