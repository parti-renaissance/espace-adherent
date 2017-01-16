<?php

namespace Tests\AppBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\TestHelperTrait;

/**
 * @method assertSame($expected, $actual)
 */
trait ControllerTestTrait
{
    use TestHelperTrait;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ObjectManager
     */
    private $manager;

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

    protected function init()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->manager = $this->container->get('doctrine.orm.entity_manager');
    }

    protected function kill()
    {
        $this->client = null;
        $this->container = null;
        $this->manager = null;
    }
}
