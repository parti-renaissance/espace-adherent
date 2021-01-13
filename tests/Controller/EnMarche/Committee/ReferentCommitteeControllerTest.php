<?php

namespace Tests\App\Controller\EnMarche\Committee;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ReferentCommitteeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites');

        $this->assertCount(4, $crawler->filter('tbody tr.committee__item'));

        $this->assertStringContainsString('En Marche - Suisse', $crawler->filter('tbody tr.committee__item')->eq(0)->text());
        $this->assertStringContainsString('0 F / 0 H', $crawler->filter('tbody tr.committee__item')->eq(0)->text());

        $this->assertStringContainsString('En Marche - Comité de Rouen', $crawler->filter('tbody tr.committee__item')->eq(1)->text());
        $this->assertStringContainsString('0 F / 2 H', $crawler->filter('tbody tr.committee__item')->eq(1)->text());

        $this->assertStringContainsString('En Marche Dammarie-les-Lys', $crawler->filter('tbody tr.committee__item')->eq(2)->text());

        $this->assertStringContainsString('Antenne En Marche de Fontainebleau', $crawler->filter('tbody tr.committee__item')->eq(3)->text());
    }

    public function testReferentCanCreateCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $crawler = $this->client->request('GET', '/parametres/mes-activites#committees');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $creationLink = $crawler->selectLink('Demander la création d\'un comité'));

        $this->client->click($creationLink->link());

        $this->assertEquals('http://'.$this->hosts['app'].'/espace-referent/comites/creer', $this->client->getRequest()->getUri());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testReferentCanSeeCommitteeRequests()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $crawler = $this->client->request('GET', '/espace-referent/comites/demandes');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $committees = $crawler->filter('table.datagrid__table-manager tbody tr'));
        $fields = $committees->first()->filter('td');
        $this->assertSame('03/01/2021', $fields->eq(0)->text());
        $this->assertStringContainsString('Une nouvelle demande', $fields->eq(1)->text());
        $this->assertSame('824 Avenue du Lys, 77190 Dammarie-les-Lys, FR', $fields->eq(2)->text());
        $this->assertSame('Referent Referent', $fields->eq(3)->text());
        $this->assertStringContainsString('En attente', $fields->eq(4)->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }
}
