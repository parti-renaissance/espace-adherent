<?php

namespace Tests\App\Controller\Renaissance\Formation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ListControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testREAdherentCanSeeFormations(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/formations');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $formations = $crawler->filter('h3');
        self::assertCount(4, $formations);
        self::assertSame('Première formation nationale', $formations->eq(0)->text());
        self::assertSame('Formation sans description', $formations->eq(1)->text());
        self::assertSame('Première formation du 77', $formations->eq(2)->text());
        self::assertSame('Deuxième formation du 77', $formations->eq(3)->text());
    }

    public function testAnonymousCanNotSeeFormations(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/formations');
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testNonREAdherentCanNotSeeFormations(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/formations');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('renaissance_host'));
    }
}
