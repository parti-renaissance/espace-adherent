<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class CitizenProjectControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCanSeeAnApprovedCitizenProject(): void
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testAnonymousUserCannotSeeAPendingCitizenProject(): void
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-marseille');
        $this->assertClientIsRedirectedTo('http://enmarche.dev/espace-adherent/connexion', $this->client);
    }

    public function testUnapprovedCitizenProjectIsViewableByAdministrator(): void
    {
        $url = '/projets-citoyens/le-projet-citoyen-a-marseille';

        // Adherent
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
        $this->logout($this->client);

        // Administrator
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
