<?php

namespace Tests\App\Controller\Api;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class IntlControllerTest extends AbstractApiTestCase
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
        ], json_decode($this->client->getResponse()->getContent(), true));
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
        ], json_decode($this->client->getResponse()->getContent(), true));
    }
}
