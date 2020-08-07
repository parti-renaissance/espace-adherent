<?php

namespace Tests\App\Controller\EnMarche\ElectedRepresentative;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class LreElectedRepresentativeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-la-republique-ensemble/elus');

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(e) municipal(e) (DIV)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
    }

    public function testListAllElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
        $crawler = $this->client->request('GET', '/');
        self::assertContains('Espace La République Ensemble', $crawler->filter('.nav-dropdown__menu__items')->text());

        $this->client->click($crawler->selectLink('Espace La République Ensemble')->link());
        $crawler = $this->client->followRedirect();
        self::assertEquals('Toutes les zones', $crawler->filter('.first-section .manager-information p > span')->text());
        self::assertCount(10, $crawler->filter('table tbody .referent__item'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->disableRepublicanSilence();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
