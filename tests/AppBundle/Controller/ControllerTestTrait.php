<?php

namespace Tests\AppBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\TestHelperTrait;

/**
 * @method assertSame($expected, $actual, $message = '')
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

    public function assertResponseStatusCode(int $statusCode, Response $response, string $message = '')
    {
        $this->assertSame($statusCode, $response->getStatusCode(), $message);
    }

    public function assertClientIsRedirectedTo(string $path, Client $client, $withSchemes = false)
    {
        $this->assertSame(
            $withSchemes ? $client->getRequest()->getSchemeAndHttpHost().$path : $path,
            $client->getResponse()->headers->get('location')
        );
    }

    public function logout(Client $client)
    {
        $client->request(Request::METHOD_GET, '/espace-adherent/deconnexion');

        return $client->followRedirect();
    }

    public function authenticateAsAdherent(Client $client, string $emailAddress, string $password)
    {
        $crawler = $client->request(Request::METHOD_GET, '/espace-adherent/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => $emailAddress,
            '_adherent_password' => $password,
        ]));

        $shouldBeRedirectedTo = $client->getRequest()->getSchemeAndHttpHost().'/evenements';

        if ($shouldBeRedirectedTo !== $client->getResponse()->headers->get('location')) {
            throw new \RuntimeException(
                'Authentication as '.$emailAddress.' failed: check the credentials used in authenticateAsAdherent() '.
                'and ensure you are properly loading adherents fixtures.'
            );
        }

        return $client->followRedirect();
    }

    protected function init(array $fixtures = [])
    {
        if ($fixtures) {
            $this->loadFixtures($fixtures);
        }

        $this->client = $this->makeClient();
        $this->container = $this->client->getContainer();
        $this->manager = $this->container->get('doctrine.orm.entity_manager');
    }

    protected function kill()
    {
        $this->loadFixtures([]);
        $this->client = null;
        $this->container = null;
        $this->manager = null;
    }
}
