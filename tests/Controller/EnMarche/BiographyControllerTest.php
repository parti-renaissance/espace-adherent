<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class BiographyControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCanSeeOurOrganization(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/notre-organisation');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains(
            'Christophe CASTANER',
            $crawler->filter('#biography .executive-officer > ul li')->text()
        );

        $this->assertContains(
            "Secrétaire d'État auprès du Premier ministre, chargé des Relations avec le Parlement.",
            $crawler->filter('#biography .executive-officer > ul li .description')->text()
        );

        $this->assertContains(
            'ABADIE Caroline',
            $crawler->filter('#biography .executive-office-members > ul li:nth-child(1)')->text()
        );

        $this->assertContains(
            'AGAMENNONE Béatrice',
            $crawler->filter('#biography .executive-office-members > ul li:nth-child(2)')->text()
        );

        $this->assertContains(
            'AVIA Laëtitia',
            $crawler->filter('#biography .executive-office-members > ul li:nth-child(3)')->text()
        );
    }

    public function testAnonymousUserCanSeeBiographyProfile(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET, '/le-mouvement/notre-organisation/christophe-castaner'
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertContains('Christophe CASTANER', $crawler->filter('#biography .profile-header h1')->text());

        $this->assertContains(
            'Délégué général du mouvement',
            $crawler->filter('#biography .profile-header span')->text()
        );

        $this->assertCount(4, $crawler->filter('#biography .profile-header .social-networks a'));

        $this->assertContains(
            'Christophe Castaner, né le 3 janvier 1966 à Ollioules, est un juriste et homme politique français.',
            $crawler->filter('#biography .profile-content p')->text()
        );
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
