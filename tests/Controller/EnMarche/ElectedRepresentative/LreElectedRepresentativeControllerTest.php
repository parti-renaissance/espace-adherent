<?php

namespace Tests\App\Controller\EnMarche\ElectedRepresentative;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class LreElectedRepresentativeControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-la-republique-ensemble/elus');

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('Conseiller(e) municipal(e) (DIV)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
