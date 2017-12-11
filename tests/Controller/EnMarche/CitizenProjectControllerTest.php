<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\Entity\CitizenProject;
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

    public function testAjaxSearchCommittee()
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame(\GuzzleHttp\json_encode([[
            'uuid' => LoadAdherentData::COMMITTEE_1_UUID,
            'name' => 'En Marche Paris 8',
        ]]), $this->client->getResponse()->getContent());

        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

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

    public function testCommitteeSupportCitizenProject()
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com', 'Champion20');

        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID);

        $this->assertFalse($citizenProject->isApproved());
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));
        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadAdherentData::COMMITTEE_4_UUID);
        $this->assertCount(0, $citizenProject->getCommitteeSupports());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));
        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertClientIsRedirectedTo(sprintf('/projets-citoyens/%s', $citizenProject->getSlug()), $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $flash = $crawler->filter('#notice-flashes');
        $this->assertSame(1, count($flash));
        $this->assertSame(sprintf('Votre comité soutient maintenant le projet citoyen %s', $citizenProject->getName()), trim($flash->text()));

        $this->manager->clear();
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(1, $citizenProject->getApprovedCommitteeSupports());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));

        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertClientIsRedirectedTo(sprintf('/projets-citoyens/%s', $citizenProject->getSlug()), $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $flash = $crawler->filter('#notice-flashes');
        $this->assertSame(1, count($flash));
        $this->assertSame(sprintf('Votre comité %s soutient déjà le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ), trim($flash->text()));

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $committeeOnSupport = $crawler->filter('#support-committee')->filter('li');
        $this->assertSame(1, $committeeOnSupport->count());
        $this->assertSame(trim($committeeOnSupport->first()->text()), $committee->getName());

        $citizenProject->removeCommitteeSupport($committee);
        $this->manager->flush();
        $this->manager->clear();

        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(0, $citizenProject->getCommitteeSupports()->toArray());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit($crawler->selectButton('Notre comité souhaite aussi soutenir ce projet')->form());
        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('#notice-flashes');
        $this->assertCount(1, $flash);
        $this->assertSame(sprintf('Votre comité soutient maintenant le projet citoyen %s', $citizenProject->getName()), trim($flash->text()));

        $this->manager->clear();
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(1, $citizenProject->getApprovedCommitteeSupports());

        $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit($crawler->selectButton('Notre comité souhaite aussi soutenir ce projet')->form());
        $crawler = $this->client->followRedirect();
        $flash = $crawler->filter('#notice-flashes');
        $this->assertCount(1, $flash);
        $this->assertSame(sprintf('Votre comité %s soutient déjà le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ), trim($flash->text()));
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
