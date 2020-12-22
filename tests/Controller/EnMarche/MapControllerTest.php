<?php

namespace Tests\App\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group map
 */
class MapControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommitteesMap()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des comités")')->count());
        $this->assertStringContainsString('44 adhérents', $crawler->filter('#counter-adherents')->text());
        $this->assertStringContainsString('12 comités', $crawler->filter('#counter-committees')->text());
        $this->assertStringContainsString('19 événements', $crawler->filter('#counter-events')->text());
    }

    public function testCommitteesEventsMap()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des événements")')->count());
        $this->assertStringContainsString('Tous (9)', $crawler->filter('.events-map-categories--all')->first()->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
