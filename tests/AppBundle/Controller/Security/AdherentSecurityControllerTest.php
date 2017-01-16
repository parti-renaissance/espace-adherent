<?php

namespace Tests\AppBundle\Controller\Security;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Repository\AdherentRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

class AdherentSecurityControllerTest extends WebTestCase
{
    /* @var Client */
    private $client;

    /* @var AdherentRepository */
    private $repository;

    use ControllerTestTrait;

    public function testAuthenticationIsSuccessful()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('form[name="app_login"]'));
        $this->assertCount(0, $crawler->filter('.login__error'));

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => 'carl999@example.fr',
            '_adherent_password' => 'secret!12345',
        ]));

        $adherent = $this->repository->findByEmail('carl999@example.fr');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/evenements', $this->client, true);
        $this->assertInstanceOf(\DateTimeImmutable::class, $adherent->getLastLoggedAt());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(2, $crawler->selectLink('Carl Mirabeau')->count());

        $this->client->click($crawler->selectLink('Carl Mirabeau')->link());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/', $this->client, true);

        $crawler = $this->client->followRedirect();
        $this->assertSame(0, $crawler->selectLink('Carl Mirabeau')->count());
    }

    /**
     * @dataProvider provideInvalidCredentials
     */
    public function testLoginCheckFails($username, $password)
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('form[name="app_login"]'));
        $this->assertCount(0, $crawler->filter('.login__error'));

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => $username,
            '_adherent_password' => $password,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('.login__error'));
        $this->assertSame('Identifiants invalides.', trim($error->text()));
    }

    public function provideInvalidCredentials()
    {
        return [
            'Unregistered adherent account' => [
                'foobar@foo.tld',
                'foo-bar-pass',
            ],
            'Registered enabled adherent' => [
                'carl999@example.fr',
                'foo-bar-pass',
            ],
            'Registered disabled account' => [
                'michelle.dufour@example.ch',
                'secret!12345',
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->repository = $this->getAdherentRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);
        $this->repository = null;
        $this->container = null;
        $this->client = null;

        parent::tearDown();
    }
}
