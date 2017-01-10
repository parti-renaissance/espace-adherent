<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractControllerTest extends WebTestCase
{
    public function assertResponseStatusCode(int $statusCode, Response $response)
    {
        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function assertClientIsRedirectedTo(string $path, Client $client)
    {
        $this->assertSame(
            $client->getRequest()->getSchemeAndHttpHost().$path,
            $client->getResponse()->headers->get('location')
        );
    }
}
