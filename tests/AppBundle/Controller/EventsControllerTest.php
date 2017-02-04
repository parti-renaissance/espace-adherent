<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class EventsControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @group functionnal
     */
    public function testIndexActionIsSecured()
    {
        $client = $this->makeClient();
        $client->request(Request::METHOD_GET, '/evenements');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());
    }
}
