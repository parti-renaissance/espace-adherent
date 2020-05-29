<?php

namespace Tests\App\Controller\EnMarche\Designation;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group voting-platform
 */
class SupervisorDesignationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testSupervisorCanSeeAllDesignationsOfHisCommittee(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8/editer');

        $this->isSuccessful($this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Désignations')->link());

        $this->assertStringEndsWith('/espace-animateur/en-marche-paris-8/designations', $crawler->getUri());

        $this->assertContains('Désignation des binômes d&#039;adhérents', $this->client->getResponse()->getContent());
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testSupervisorCanSeeSubscribedVotersCommittee(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-paris-8/designations');
        $link = $crawler->selectLink('Voir les détails')->link();
        $crawler = $this->client->click($link);

        $this->isSuccessful($this->client->getResponse());
        $this->assertStringEndsWith('/liste-emargement', $crawler->getUri());

        $overviewCards = $crawler->filter('.manager-overview__case');
        $this->assertCount(3, $overviewCards);

        // Voter box
        $votersBox = $overviewCards->eq(0);
        $this->assertContains('10', trim($votersBox->filter('.manager-overview__case--value')->text()));
        $this->assertContains('Inscrits', trim($votersBox->filter('.manager-overview__case--title')->text()));

        // Vote box
        $votesBox = $overviewCards->eq(1);
        $this->assertContains('80%', trim($votesBox->filter('.manager-overview__case--value')->text()));
        $this->assertContains('Participants', trim($votesBox->filter('.manager-overview__case--title')->text()));

        // Candidate box
        $candidateBox = $overviewCards->eq(2);
        $this->assertContains('5Femme', trim($candidateBox->filter('.manager-overview__case--value')->text()));
        $this->assertContains('5Homme', trim($candidateBox->filter('.manager-overview__case--value')->text()));
        $this->assertContains('Candidatures', trim($candidateBox->filter('.manager-overview__case--title')->text()));

        $this->assertContains('Liste des inscrits / émargements', $crawler->filter('.datagrid__pre-table')->eq(1)->text());

        $this->assertCount(10, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertContains('Bob Assesseur', $crawler->filter('.datagrid__table-manager tbody')->text());
    }

    public function testSupervisorCanSeeCandidateResultsPage(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-paris-8/designations');
        $crawler = $this->client->click($crawler->selectLink('Voir les détails')->link());
        $crawler = $this->client->click($crawler->selectLink('Résultats Femme')->link());

        $this->isSuccessful($this->client->getResponse());
        $this->assertStringEndsWith('/resultats?femme=1', $crawler->getUri());

        $this->assertContains('Résultats par scrutin : Femme', $crawler->filter('.datagrid__pre-table')->eq(1)->text());

        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Résultats Homme')->link());

        $this->isSuccessful($this->client->getResponse());
        $this->assertStringEndsWith('/resultats?homme=1', $crawler->getUri());

        $this->assertContains('Résultats par scrutin : Homme', $crawler->filter('.datagrid__pre-table')->eq(1)->text());

        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testSupervisorCanSeeVoteListPage(): void
    {
        self::authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-animateur/en-marche-paris-8/designations');
        $crawler = $this->client->click($crawler->selectLink('Voir les détails')->link());
        $crawler = $this->client->click($crawler->selectLink('Bulletins dépouillés')->link());

        $this->isSuccessful($this->client->getResponse());
        $this->assertStringEndsWith('/bulletins', $crawler->getUri());

        $this->assertContains('Liste des bulletins dépouillés', $crawler->filter('.datagrid__pre-table')->eq(1)->text());

        $this->assertCount(8, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }
}
