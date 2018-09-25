<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadExecutiveOfficeMemberData;
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
            $crawler->filter('#executive-officer > ul li')->text()
        );

        $this->assertContains(
            'ABADIE Caroline',
            $crawler->filter('#executive-office-members > ul li:nth-child(1)')->text()
        );

        $this->assertContains(
            'AGAMENNONE Béatrice',
            $crawler->filter('#executive-office-members > ul li:nth-child(2)')->text()
        );

        $this->assertContains(
            'AVIA Laëtitia',
            $crawler->filter('#executive-office-members > ul li:nth-child(3)')->text()
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadExecutiveOfficeMemberData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
