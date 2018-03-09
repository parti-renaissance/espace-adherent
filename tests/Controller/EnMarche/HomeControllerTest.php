<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadLiveLinkData;
use AppBundle\DataFixtures\ORM\LoadRedirectionData;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 * @group home
 */
class HomeControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testIndex(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->isSuccessful($response = $this->client->getResponse());

        // Articles
        // $this->assertSame(1, $crawler->filter('html:contains("« Je viens échanger, comprendre et construire. »")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Tribune de Richard Ferrand")')->count());

        // Live links
        $this->assertSame(1, $crawler->filter('html:contains("Guadeloupe")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Le candidat du travail")')->count());
    }

    public function testHealth(): void
    {
        $this->client->request(Request::METHOD_GET, '/health');

        $this->isSuccessful($this->client->getResponse());
    }

    /**
     * @dataProvider provideSitemaps
     */
    public function testSitemaps(string $page): void
    {
        $this->client->request(Request::METHOD_GET, $page);

        $this->isSuccessful($this->client->getResponse());
    }

    /**
     * @dataProvider provideEmptySitemaps
     */
    public function testEmptySitemaps(string $page): void
    {
        $this->client->request(Request::METHOD_GET, $page);

        $this->assertClientIsRedirectedTo('/sitemap.xml', $this->client, false, true);
    }

    public function provideSitemaps(): array
    {
        return [
            ['/sitemap.xml'],
            ['/sitemap_main_1.xml'],
            ['/sitemap_content_1.xml'],
            ['/sitemap_committees_1.xml'],
            ['/sitemap_events_1.xml'],
            ['/sitemap_images_1.xml'],
        ];
    }

    public function provideEmptySitemaps(): array
    {
        return [
            ['/sitemap_committees_42.xml'],
            ['/sitemap_events_42.xml'],
        ];
    }

    public function testDynamicRedirections(): void
    {
        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-301');

        $this->assertClientIsRedirectedTo('/dynamic-redirection-301-target', $this->client, false, true);

        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-302');

        $this->assertClientIsRedirectedTo('/dynamic-redirection-302-target', $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadLiveLinkData::class,
            LoadRedirectionData::class,
            LoadEventData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
