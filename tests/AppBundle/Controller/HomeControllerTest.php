<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Articles
        $this->assertEquals(1, $crawler->filter('html:contains("« Je viens échanger, comprendre et construire. »")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("Tribune de Richard Ferrand")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("Signez l’appel « Elles Marchent »")')->count());

        // Live links
        $this->assertEquals(1, $crawler->filter('html:contains("Guadeloupe")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("Le candidat du travail")')->count());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $client->getResponse()->headers->get('cache-control'));
    }

    public function testArticle()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/article/outre-mer');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $client->getResponse()->headers->get('cache-control'));
    }

    public function testHealth()
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $client->getResponse()->headers->get('cache-control'));
    }
}
