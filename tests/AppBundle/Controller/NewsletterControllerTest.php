<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NewsletterControllerTest extends WebTestCase
{
    public function testNewsletter()
    {
        $client = static::createClient();
        $client->request('GET', '/newsletter');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
