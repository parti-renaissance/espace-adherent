<?php

namespace Tests\App\Controller\EnMarche\ElectedRepresentative;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class SenatorialCandidateElectedRepresentativeControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'senatorial-candidate@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-senatoriales/elus');

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('Nord Département', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('Sénateur(rice)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertStringContainsString('Nord (59)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
