<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class TerritorialCouncilControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testNotMemberOfATerritorialCouncil()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
        $crawler = $this->client->request('GET', '/');
        self::assertCount(0, $crawler->filter('header nav .nav-dropdown a:contains("Conseil territorial")'));

        $this->client->request('GET', '/conseil-territorial');
        self::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testMembers()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request('GET', '/');

        self::assertCount(1, $crawler->filter('header nav .nav-dropdown a:contains("Conseil territorial")'));

        $crawler = $this->client->click($crawler->selectLink('Conseil territorial')->link());
        self::assertEquals('http://test.enmarche.code/conseil-territorial', $crawler->getUri());

        $members = $crawler->filter('#territorial-council-members .territorial-council__member');
        self::assertCount(6, $members);
        self::assertContains('Jacques Picard', $members->first()->text());
        self::assertContains('Lucie Olivera', $members->eq(1)->text());
        self::assertContains('Referent Referent', $members->eq(2)->text());

        self::assertCount(1, $crawler->filter('.territorial-council__aside h5:contains("Président du Conseil territorial")'));
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
