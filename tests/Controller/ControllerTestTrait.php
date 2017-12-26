<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\CitizenInitiativeCategory;
use AppBundle\Entity\EventCategory;
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

    private $hosts = [];

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

        $shouldBeRedirectedTo = 'http://'.$this->hosts['app'].'/evenements';

        if ($shouldBeRedirectedTo !== $client->getResponse()->headers->get('location')) {
            $this->fail(
                'Authentication as '.$emailAddress.' failed: check the credentials used in authenticateAsAdherent() '.
                'and ensure you are properly loading adherents fixtures.'
            );
        }

        return $client->followRedirect();
    }

    protected function appendCollectionFormPrototype(\DOMElement $collection, string $newIndex = '0', string $prototypeName = '__name__'): void
    {
        $prototypeHTML = $collection->getAttribute('data-prototype');
        $prototypeHTML = str_replace($prototypeName, $newIndex, $prototypeHTML);
        $prototypeFragment = new \DOMDocument();
        $prototypeFragment->loadHTML($prototypeHTML);
        foreach ($prototypeFragment->getElementsByTagName('body')->item(0)->childNodes as $prototypeNode) {
            $collection->appendChild($collection->ownerDocument->importNode($prototypeNode, true));
        }
    }

    private function getEventCategoryIdForName(string $categoryName): int
    {
        return $this->manager->getRepository(EventCategory::class)->findOneBy(['name' => $categoryName])->getId();
    }

    private function getCitizenInitiativeCategoryIdForName(string $categoryName): int
    {
        return $this->manager->getRepository(CitizenInitiativeCategory::class)->findOneBy(['name' => $categoryName])->getId();
    }

    protected function init(array $fixtures = [], string $host = 'app')
    {
        if ($fixtures) {
            $this->loadFixtures($fixtures);
        }

        $this->container = $this->getContainer();

        $this->hosts['app'] = $this->container->getParameter('app_host');
        $this->hosts['amp'] = $this->container->getParameter('amp_host');
        $this->hosts['legislatives'] = $this->container->getParameter('legislatives_host');

        $this->client = $this->makeClient(false, ['HTTP_HOST' => $this->hosts[$host]]);
        $this->manager = $this->container->get('doctrine.orm.entity_manager');
    }

    protected function kill()
    {
        $this->loadFixtures([]);
        $this->client = null;
        $this->container = null;
        $this->manager = null;
        $this->hosts = [];
    }
}
