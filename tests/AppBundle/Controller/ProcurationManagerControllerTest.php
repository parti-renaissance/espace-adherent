<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class ProcurationManagerControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @group functionnal
     * @dataProvider providePages
     */
    public function testProcurationManagerBackendIsForbiddenAsAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('http://localhost/espace-adherent/connexion', $this->client);
    }

    /**
     * @group functionnal
     * @dataProvider providePages
     */
    public function testProcurationManagerBackendIsForbiddenAsAdherentNotReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    /**
     * @group functionnal
     * @dataProvider providePages
     */
    public function testProcurationManagerBackendIsAccessibleAsReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function providePages()
    {
        return [
            ['/espace-responsable-procuration'],
            ['/espace-responsable-procuration/demande/1'],
            ['/espace-responsable-procuration/demande/2'],
        ];
    }

    /**
     * @group functionnal
     */
    public function testProcurationManagerNotManagedRequestIsForbidden()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/demande/4');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadProcurationData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
