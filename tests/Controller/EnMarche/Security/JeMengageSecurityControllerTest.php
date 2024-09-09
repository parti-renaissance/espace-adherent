<?php

namespace Tests\App\Controller\EnMarche\Security;

use App\DataFixtures\ORM\LoadAdherentData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('security')]
class JeMengageSecurityControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideAdherentWithoutRole')]
    public function testAdherentWithoutRoleCannotConnectToJeMengageApp(string $email): void
    {
        $this->client->request(Request::METHOD_GET, '/oauth/v2/auth?response_type=code&client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Me connecter')->form([
            '_login_email' => $email,
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://test.renaissance.code/oauth/v2/auth?client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c&response_type=code', $this->client);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    #[DataProvider('provideAdherentWithCorrectRole')]
    public function testAdherentWithGoodRoleCanConnectToJeMengageApp(string $email): void
    {
        $this->client->request(Request::METHOD_GET, '/oauth/v2/auth?response_type=code&client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c&scope=jemengage_admin');
        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Me connecter')->form([
            '_login_email' => $email,
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://test.renaissance.code/oauth/v2/auth?client_id=4498e44f-f214-110d-8b76-98a83f9d2b0c&response_type=code&scope=jemengage_admin', $this->client);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $response = $this->client->getResponse());
        $this->assertStringStartsWith('http://localhost:3000/auth?code=', $response->headers->get('location'));
    }

    public static function provideAdherentWithoutRole(): iterable
    {
        yield ['carl999@example.fr'];
        yield ['adherent-male-a@en-marche-dev.fr'];
    }

    public static function provideAdherentWithCorrectRole(): iterable
    {
        yield ['gisele-berthoux@caramail.com'];
        yield ['president-ad@renaissance-dev.fr'];
        yield ['adherent-male-55@en-marche-dev.fr'];
    }
}
