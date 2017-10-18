<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Config;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 * @group coordinator
 */
class CoordinatorControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider providePages
     */
    public function testCoordinatorBackendIsForbiddenForAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedToAuth();
    }

    /**
     * @dataProvider providePages
     */
    public function testCoordinatorBackendIsForbiddenForAdherentNotCoordinator($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testCoordinatorBackendIsAccessibleForCoordinator($path)
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testValidationCommitteeFailed()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->click($this->client->getCrawler()->selectLink('Espace coordinateur régional')->link());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());

        $data = [];

        $this->client->submit($this->client->getCrawler()->selectButton('Pré-accepter')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->client->followRedirect();

        $this->assertSame(1, $this->client->getCrawler()->filter('#committee-list .coordinator__item .form__errors')->count());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            trim($this->client->getCrawler()->filter('#committee-list .coordinator__item .form__errors')->text()));

        $data = [];
        $data['coordinator_committee']['coordinatorComment'] = 'test';
        $data['coordinator_committee']['accept'] = null;
        $this->client->submit($this->client->getCrawler()->selectButton('Pré-accepter')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->client->followRedirect();

        $this->assertSame(1, $this->client->getCrawler()->filter('#committee-list .coordinator__item .form__errors')->count());
        $this->assertSame('Vous devez saisir au moins 10 caractères.',
            trim($this->client->getCrawler()->filter('#committee-list .coordinator__item .form__errors')->text()));
    }

    public function testPreAcceptCommitteeWithSuccess()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->click($this->client->getCrawler()->selectLink('Espace coordinateur régional')->link());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());

        $data = [];
        $data['coordinator_committee']['coordinatorComment'] = 'Mon commentaire sur l\'AL';
        $data['coordinator_committee']['accept'] = null;
        $this->client->submit($this->client->getCrawler()->selectButton('Pré-accepter')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/espace-coordinateur/comites', $this->client);
        $this->client->followRedirect();

        $this->assertSame(0, $this->client->getCrawler()->filter('#committee-list .coordinator__item .form__errors')->count());
        $this->assertSame('Merci. Votre appréciation a été transmise à nos équipes.', $this->client->getCrawler()->filter('#notice-flashes > .flash__inner')->text());
        $this->assertContains('Aucun comité ne repond à ce filtre', $this->client->getCrawler()->filter('.coordinator-committee-manager__content')->text());

        $this->client->request(Request::METHOD_GET, '/espace-coordinateur/comites?s=PRE_APPROVED');

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertContains('Mon commentaire sur', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertCount(1, $this->client->getCrawler()->filter('.fa-check-circle'));
    }

    public function testPreRefuseCommitteeWithSuccess()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->click($this->client->getCrawler()->selectLink('Espace coordinateur régional')->link());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());

        $data = [];
        $data['coordinator_committee']['coordinatorComment'] = 'Mon commentaire sur l\'AL';
        $data['coordinator_committee']['refuse'] = null;
        $this->client->submit($this->client->getCrawler()->selectButton('Pré-refuser')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/espace-coordinateur/comites', $this->client);
        $this->client->followRedirect();

        $this->assertSame(0, $this->client->getCrawler()->filter('#committee-list .coordinator__item .form__errors')->count());
        $this->assertSame('Merci. Votre appréciation a été transmise à nos équipes.', $this->client->getCrawler()->filter('#notice-flashes > .flash__inner')->text());
        $this->assertContains('Aucun comité ne repond à ce filtre', $this->client->getCrawler()->filter('.coordinator-committee-manager__content')->text());

        $this->client->request(Request::METHOD_GET, '/espace-coordinateur/comites?s=PRE_REFUSED');

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertContains('Mon commentaire sur', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertCount(1, $this->client->getCrawler()->filter('.fa-times'));
    }

    public function providePages()
    {
        return [
            ['/espace-coordinateur/comites'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
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
