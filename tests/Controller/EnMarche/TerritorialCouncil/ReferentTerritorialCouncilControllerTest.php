<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ReferentElectedRepresentativeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListTerritorialCouncilMembers()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/conseil-territorial/membres');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(7, $crawler->filter('tbody tr.referent__item'));
        $this->assertCount(7, $crawler->filter('.status.status__1'));
        $this->assertContains('Berthoux Gisele', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(ère) départemental(e) 75', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(ère) consulaire 75', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(ère) municipal(e) 75010', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('02/02/2020', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('+33 1 38 76 43 34', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Non', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Abonné(e)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
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
