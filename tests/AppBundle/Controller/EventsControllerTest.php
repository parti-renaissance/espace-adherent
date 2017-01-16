<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventsControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testIndexActionIsSecured()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/evenements');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());
    }
}
