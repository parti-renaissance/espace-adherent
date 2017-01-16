<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadArticleData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadLiveLinkData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var Client
     */
    private $client;

    public function testIndex()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());

        // Articles
        $this->assertSame(1, $crawler->filter('html:contains("« Je viens échanger, comprendre et construire. »")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Tribune de Richard Ferrand")')->count());

        // Live links
        $this->assertSame(1, $crawler->filter('html:contains("Guadeloupe")')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Le candidat du travail")')->count());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $response->headers->get('cache-control'));
    }

    public function testArticle()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/article/outre-mer');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $response->headers->get('cache-control'));
    }

    public function testHealth()
    {
        $this->client->request(Request::METHOD_GET, '/health');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $response->headers->get('cache-control'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadHomeBlockData::class,
            LoadLiveLinkData::class,
            LoadArticleData::class,
        ]);

        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->client = null;

        parent::tearDown();
    }
}
