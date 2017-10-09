<?php

namespace Tests\AppBundle\Security;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadAdminData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group time-sensitive
 * @group functional
 */
class InactiveAdminDisconnectionTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testLogoutInactiveAdmin()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        // connect as admin
        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_admin_email' => 'admin@en-marche-dev.fr',
            '_admin_password' => 'admin',
        ]));

        $this->client->request(Request::METHOD_GET, '/admin/app/media/list');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // wait
        sleep(1900);

        // go to another page
        $this->client->request(Request::METHOD_GET, '/admin/dashboard');

        // should be redirected to logout
        $this->assertClientIsRedirectedTo('/admin/logout', $this->client, false);
    }

    public function testNoLogoutInactiveAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        // wait
        sleep(1900);

        // go to another page
        $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte');

        // status code should be 200 OK, because there is no redirection to disconnect
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdminData::class,
            LoadAdherentData::class,
            LoadHomeBlockData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
