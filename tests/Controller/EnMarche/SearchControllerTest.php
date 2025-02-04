<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\Event\Event;
use App\Search\SearchParametersFilter;
use Cake\Chronos\Chronos;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class SearchControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideQuery')]
    public function testIndex($query)
    {
        $this->client->request(Request::METHOD_GET, '/recherche', $query);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testSearchEvents()
    {
        Chronos::setTestNow('2018-05-18');

        $crawler = $this->client->request(Request::METHOD_GET, '/recherche', [
            'r' => 25,
            'c' => 'Melun, France',
            't' => 'events',
            'offset' => 0,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(2, $crawler->filter('.search__results__row'));

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/recherche', [
            'r' => 25,
            'c' => 'Melun, France',
            't' => 'events',
            'offset' => 0,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(3, $crawler->filter('.search__results__row'));

        Chronos::setTestNow();
    }

    #[DataProvider('providerPathSearchPage')]
    public function testAccessSearchPage(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public static function providerPathSearchPage(): array
    {
        return [
            ['/evenements'],
            ['/comites'],
            ['/recherche'],
        ];
    }

    public static function provideQuery(): \Generator
    {
        yield 'No criteria' => [[]];
        yield 'Search committees' => [[SearchParametersFilter::PARAMETER_TYPE => SearchParametersFilter::TYPE_COMMITTEES]];
        yield 'Search events' => [[SearchParametersFilter::PARAMETER_TYPE => SearchParametersFilter::TYPE_EVENTS]];
    }

    public function testListAllEvents()
    {
        /** @var Paginator $evenets */
        $events = $this->getRepository(Event::class)->paginate();

        $this->client->request(Request::METHOD_GET, '/tous-les-evenements/3');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request(Request::METHOD_GET, '/tous-les-evenements/1');

        $this->assertSame(30, $crawler->filter('div.search__results__row')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="prev"]')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="next"]')->count());
        $this->assertSame(3, $crawler->filter('.listing__paginator li')->count());
        $this->assertSame('/tous-les-evenements', $crawler->filter('.listing__paginator li a')->attr('href'));
        $this->assertSame('1', trim($crawler->filter('.listing__paginator li a')->text()));
    }

    public function testListEventsAsAdherent()
    {
        Chronos::setTestNow('2018-05-18');

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(8, $crawler->filter('div.search__results__row')->count());

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/categorie/conference-debat');

        $this->assertSame(2, $crawler->filter('div.search__results__row')->count());
        $this->assertSame('Conférence-débat', $crawler->filter('.search__results__info .search__results__tag div')->text());
        $this->assertSame('Réunion de réflexion évryenne', trim($crawler->filter('.search__results__info .search__results__meta h2 a')->text()));

        $this->client->request(Request::METHOD_GET, '/evenements/categorie/inexistante');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/evenements', $this->client);

        Chronos::setTestNow();
    }

    public function testListEventsByCategory()
    {
        Chronos::setTestNow('2018-05-18');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(8, $crawler->filter('div.search__results__row')->count());

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/categorie/conference-debat');

        $this->assertSame(2, $crawler->filter('div.search__results__row')->count());
        $this->assertSame('Conférence-débat', $crawler->filter('.search__results__info .search__results__tag div')->text());
        $this->assertSame('Réunion de réflexion évryenne', trim($crawler->filter('.search__results__info .search__results__meta h2 a')->text()));

        $this->client->request(Request::METHOD_GET, '/evenements/categorie/inexistante');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/evenements', $this->client);

        Chronos::setTestNow();
    }

    public function testListAllCommittee()
    {
        $this->client->request(Request::METHOD_GET, '/tous-les-comites/3');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request(Request::METHOD_GET, '/tous-les-comites/1');

        $this->assertSame(16, $crawler->filter('.search__committee__box')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="prev"]')->count());
        $this->assertSame(0, $crawler->filter('meta[rel="next"]')->count());
        $this->assertSame(1, $crawler->filter('.listing__paginator li')->count());
        $this->assertSame('/tous-les-comites', $crawler->filter('.listing__paginator li a')->attr('href'));
        $this->assertSame('1', trim($crawler->filter('.listing__paginator li a')->text()));
    }
}
