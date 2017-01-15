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
    /**
     * @var Client
     */
    private $client;

    public function testIndex()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Articles
        $this->assertEquals(1, $crawler->filter('html:contains("« Je viens échanger, comprendre et construire. »")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("Tribune de Richard Ferrand")')->count());

        // Live links
        $this->assertEquals(1, $crawler->filter('html:contains("Guadeloupe")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("Le candidat du travail")')->count());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $this->client->getResponse()->headers->get('cache-control'));
    }

    public function testArticle()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/article/outre-mer');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $this->client->getResponse()->headers->get('cache-control'));
    }

    public function testHealth()
    {
        $this->client->request(Request::METHOD_GET, '/health');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $this->client->getResponse()->headers->get('cache-control'));
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
