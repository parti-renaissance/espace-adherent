<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DonationControllerTest extends WebTestCase
{
    public function testFullProcess()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/don');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO
    }
}
