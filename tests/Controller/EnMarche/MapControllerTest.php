<?php

namespace Tests\App\Controller\EnMarche;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('map')]
class MapControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testCommitteesMap()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des comités")')->count());
        $this->assertStringContainsString('75 adhérents', $crawler->filter('#counter-adherents')->text());
        $this->assertStringContainsString('13 comités', $crawler->filter('#counter-committees')->text());
        $this->assertStringContainsString('18 événements', $crawler->filter('#counter-events')->text());
    }

    public function testCommitteesMapAsAdherent()
    {
        Chronos::setTestNow('2018-05-18');

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des comités")')->count());
        $this->assertStringContainsString('75 adhérents', $crawler->filter('#counter-adherents')->text());
        $this->assertStringContainsString('13 comités', $crawler->filter('#counter-committees')->text());
        $this->assertStringContainsString('19 événements', $crawler->filter('#counter-events')->text());

        Chronos::setTestNow();
    }

    public function testCommitteesEventsMap()
    {
        Chronos::setTestNow('2018-05-18');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des événements")')->count());
        $this->assertStringContainsString('Tous (8)', $crawler->filter('.events-map-categories--all')->first()->text());

        Chronos::setTestNow();
    }

    public function testCommitteesEventsMapAsAdherent()
    {
        Chronos::setTestNow('2018-05-18');

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("La carte des événements")')->count());
        $this->assertStringContainsString('Tous (9)', $crawler->filter('.events-map-categories--all')->first()->text());

        Chronos::setTestNow();
    }
}
