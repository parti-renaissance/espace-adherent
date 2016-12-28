<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IntlControllerTest extends WebTestCase
{
    public function testGetPostalCode()
    {
        $client = static::createClient();
        $client->request('GET', '/api/postal-code/35420');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertEquals([
            35018 => 'La Bazouge-du-Désert',
            35111 => 'Le Ferré',
            35162 => 'Louvigné-du-Désert',
            35174 => 'Mellé',
            35190 => 'Monthault',
            35230 => 'Poilley',
            35271 => 'Saint-Georges-de-Reintembault',
            35357 => 'Villamée',
        ], \GuzzleHttp\json_decode($client->getResponse()->getContent(), true));
    }
}
