<?php

namespace Tests\App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class IntlControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testGetPostalCode()
    {
        $this->client->request(Request::METHOD_GET, '/api/postal-code/94440');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals([
            94048 => 'Marolles-en-Brie',
            94070 => 'Santeny',
            94075 => 'Villecresnes',
        ], \GuzzleHttp\json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testGetVoteOffices()
    {
        $this->client->request(Request::METHOD_GET, '/api/vote-offices/DE');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertEquals([
            'Berlin',
            'Dusseldorf',
            'Hambourg',
            'Francfort',
            'Munich',
            'Nuremberg',
            'Sarrebruck',
            'Stuttgart',
        ], \GuzzleHttp\json_decode($this->client->getResponse()->getContent(), true));
    }
}
