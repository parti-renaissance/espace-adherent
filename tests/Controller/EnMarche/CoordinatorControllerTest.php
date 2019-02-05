<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group coordinator
 */
class CoordinatorControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider providePages
     */
    public function testCoordinatorBackendIsForbiddenForAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
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

    public function testCoordinatorCommitteeBackendIsAccessibleForCoordinator()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coordinateur/comites/list');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testCoordinatorCitizenProjectBackendIsAccessibleForCoordinator()
    {
        $this->authenticateAsAdherent($this->client, 'coordinatrice-cp@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coordinateur/projet-citoyen/list');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testValidationCommitteeFailed()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/evenements');
        $this->client->click($this->client->getCrawler()->selectLink('Espace coordinateur régional')->link());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());

        $data = [];

        $this->client->submit($this->client->getCrawler()->selectButton('Pré-accepter')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->client->followRedirect();

        $this->assertSame(1, $this->client->getCrawler()->filter('#committee-list .coordinator__item .form__error')->count());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            trim($this->client->getCrawler()->filter('#committee-list .coordinator__item .form__error')->text()));

        $data = [];
        $data['coordinator_area']['coordinatorComment'] = 'test';
        $data['coordinator_area']['accept'] = null;
        $this->client->submit($this->client->getCrawler()->selectButton('Pré-accepter')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->client->followRedirect();

        $this->assertSame(1, $this->client->getCrawler()->filter('#committee-list .coordinator__item .form__error')->count());
        $this->assertSame('Vous devez saisir au moins 10 caractères.',
            trim($this->client->getCrawler()->filter('#committee-list .coordinator__item .form__error')->text()));
    }

    public function testPreAcceptCommitteeWithSuccess()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/evenements');
        $this->client->click($this->client->getCrawler()->selectLink('Espace coordinateur régional')->link());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());

        $data = [];
        $data['coordinator_area']['coordinatorComment'] = 'Mon commentaire sur l\'AL';
        $data['coordinator_area']['accept'] = null;
        $this->client->submit($this->client->getCrawler()->selectButton('Pré-accepter')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/espace-coordinateur/comites/list', $this->client);
        $crawler = $this->client->followRedirect();

        $this->assertSame(0, $crawler->filter('#committee-list .coordinator__item .form__error')->count());
        $this->seeFlashMessage($crawler, 'Merci. Votre appréciation a été transmise à nos équipes.');
        $this->assertContains('Aucun comité ne répond à ce filtre', $crawler->filter('.coordinator-area__content')->text());

        $this->client->request(Request::METHOD_GET, '/espace-coordinateur/comites/list?s=PRE_APPROVED');

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertContains('Mon commentaire sur', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertCount(1, $this->client->getCrawler()->filter('.fa-check-circle'));
    }

    public function testPreRefuseCommitteeWithSuccess()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/evenements');
        $this->client->click($this->client->getCrawler()->selectLink('Espace coordinateur régional')->link());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());

        $data = [];
        $data['coordinator_area']['coordinatorComment'] = 'Mon commentaire sur l\'AL';
        $data['coordinator_area']['refuse'] = null;
        $this->client->submit($this->client->getCrawler()->selectButton('Pré-refuser')->form(), $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/espace-coordinateur/comites/list', $this->client);
        $crawler = $this->client->followRedirect();

        $this->assertSame(0, $crawler->filter('#committee-list .coordinator__item .form__error')->count());
        $this->seeFlashMessage($crawler, 'Merci. Votre appréciation a été transmise à nos équipes.');
        $this->assertContains('Aucun comité ne répond à ce filtre', $this->client->getCrawler()->filter('.coordinator-area__content')->text());

        $this->client->request(Request::METHOD_GET, '/espace-coordinateur/comites/list?s=PRE_REFUSED');

        $this->assertContains('En Marche Marseille 3', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertContains('Mon commentaire sur', $this->client->getCrawler()->filter('#committee-list')->text());
        $this->assertCount(1, $this->client->getCrawler()->filter('.fa-times'));
    }

    public function providePages()
    {
        return [
            ['/espace-coordinateur/comites/list'],
            ['/espace-coordinateur/projet-citoyen/list'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
