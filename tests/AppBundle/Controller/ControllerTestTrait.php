<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\TestHelperTrait;

/**
 * @method assertSame($expected, $actual)
 */
trait ControllerTestTrait
{
    use TestHelperTrait;

    public function assertResponseStatusCode(int $statusCode, Response $response)
    {
        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function assertClientIsRedirectedTo(string $path, Client $client, $withSchemes = false)
    {
        $this->assertSame(
            $withSchemes ? $client->getRequest()->getSchemeAndHttpHost().$path : $path,
            $client->getResponse()->headers->get('location')
        );
    }
}
