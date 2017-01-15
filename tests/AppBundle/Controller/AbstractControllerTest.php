<?php

namespace Tests\AppBundle\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\TestHelperTrait;

abstract class AbstractControllerTest extends WebTestCase
{
    use TestHelperTrait;

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
