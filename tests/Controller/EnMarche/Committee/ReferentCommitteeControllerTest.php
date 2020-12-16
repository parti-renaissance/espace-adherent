<?php

namespace Tests\App\Controller\EnMarche\Committee;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }
}
