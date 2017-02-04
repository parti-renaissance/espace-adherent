<?php

namespace Tests\AppBundle\Controller\Security;

use AppBundle\DataFixtures\ORM\LoadAdminData;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class AdminSecurityControllerTest extends SqliteWebTestCase
{
    /* @var Client */
    private $client;

    use ControllerTestTrait;

    /**
     * @group functionnal
     */
    public function testAuthenticationIsSuccessful()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('form[name="app_login"]'));
        $this->assertCount(0, $crawler->filter('.login__error'));

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_admin_email' => 'titouan.galopin@en-marche.fr',
            '_admin_password' => 'secret!12345',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/admin/dashboard', $this->client, true);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    /**
     * @group functionnal
     * @dataProvider provideInvalidCredentials
     */
    public function testLoginCheckFails($username, $password)
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('form[name="app_login"]'));
        $this->assertCount(0, $crawler->filter('.login__error'));

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_admin_email' => $username,
            '_admin_password' => $password,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/admin/login', $this->client, true);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('.login__error'));
        $this->assertSame('Identifiants invalides.', trim($error->text()));
    }

    public function provideInvalidCredentials()
    {
        return [
            'Valid username, invalid password' => [
                'titouan.galopin@en-marche.fr',
                'foo-bar-pass',
            ],
            'Invalid username, valid password' => [
                'carl999@example.fr',
                'secret!12345',
            ],
            'Invalid username, invalid password' => [
                'carl999@example.fr',
                'foo-bar-pass',
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdminData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
