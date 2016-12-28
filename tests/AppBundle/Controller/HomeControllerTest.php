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
    }
}
