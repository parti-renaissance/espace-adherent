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
