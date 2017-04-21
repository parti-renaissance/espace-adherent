<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadLiveLinkData;
use AppBundle\DataFixtures\ORM\LoadRedirectionData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functionnal
 */
class HomeControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());

        // Articles
        // $this->assertSame(1, $crawler->filter('html:contains("« Je viens échanger, comprendre et construire. »")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Tribune de Richard Ferrand")')->count());

        // Live links
        $this->assertSame(1, $crawler->filter('html:contains("Guadeloupe")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Le candidat du travail")')->count());
    }

    public function testHealth()
    {
        $this->client->request(Request::METHOD_GET, '/health');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    /**
     * @dataProvider provideSitemaps
     */
    public function testSitemaps($page)
    {
        $this->client->request(Request::METHOD_GET, $page);

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    public function provideSitemaps()
    {
        return [
            ['/sitemap.xml'],
            ['/sitemap_main_1.xml'],
            ['/sitemap_content_1.xml'],
            ['/sitemap_committees_1.xml'],
            ['/sitemap_events_1.xml'],
        ];
    }

    public function testDynamicRedirections()
    {
        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-301');
        $this->assertResponseStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/dynamic-redirection-301-target', $this->client);

        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-302');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/dynamic-redirection-302-target', $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadLiveLinkData::class,
            LoadRedirectionData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
