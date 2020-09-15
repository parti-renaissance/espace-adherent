<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class PoliticalCommitteeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testTerritorialCouncilMemberButNotMemberOfAPoliticalCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request('GET', '/');
        self::assertCount(1, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $crawler = $this->client->click($crawler->selectLink('Mes instances')->link());
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites#instances', $crawler->getUri());

        self::assertCount(1, $crawler->filter('#territorial_council'));
        self::assertCount(0, $crawler->filter('#political_committee'));

        $this->client->request('GET', '/comite-politique');
        self::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testMemberOfInactivePoliticalCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request('GET', '/');
        self::assertCount(1, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $crawler = $this->client->click($crawler->selectLink('Mes instances')->link());
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites#instances', $crawler->getUri());

        self::assertCount(1, $crawler->filter('#territorial_council'));
        self::assertCount(0, $crawler->filter('#political_committee'));

        $this->client->request('GET', '/comite-politique');
        self::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testMembers()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request('GET', '/');

        self::assertCount(1, $crawler->filter('header .em-nav-dropdown a:contains("Mes instances")'));

        $crawler = $this->client->click($crawler->selectLink('Mes instances')->link());
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites#instances', $crawler->getUri());

        self::assertCount(1, $crawler->filter('article#territorial_council'));
        self::assertCount(1, $crawler = $crawler->filter('article#political_committee'));

        $crawler = $this->client->click($crawler->selectLink('Voir')->link());
        self::assertEquals('http://test.enmarche.code/comite-politique', $crawler->getUri());

        self::assertCount(1, $crawler->filter('.territorial-council__aside h5:contains("Président du Comité politique")'));
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
