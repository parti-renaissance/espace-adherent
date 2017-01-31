<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class IntlControllerTest extends SqliteWebTestCase
{
    public function testGetPostalCode()
    {
        $client = $this->makeClient();
        $client->request(Request::METHOD_GET, '/api/postal-code/35420');

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
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
