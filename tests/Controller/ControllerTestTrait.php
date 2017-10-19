<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiativeCategory;
use AppBundle\Entity\EventCategory;
use Doctrine\Common\Persistence\ObjectManager;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Config;
use Tests\AppBundle\TestHelperTrait;

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

    public function assertClientIsRedirectedToAuth()
    {
        $redirectUrl = str_replace('http://localhost', '', rtrim($this->client->getResponse()->headers->get('location'), '/'));
        $this->assertNotNull($redirectUrl);

        $this->assertSame('/connect/auth', $redirectUrl);
    }

    public function logout(Client $client)
    {
        $client->request(Request::METHOD_GET, '/logout');
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        return $client;
    }

    public function authenticateAsAdherent(Client $client, string $emailAddress)
    {
        $session = $client->getContainer()->get('session');

        /** @var Adherent $user */
        $user = $client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(Adherent::class)
            ->findOneBy(['emailAddress' => $emailAddress]);

        $token = new OAuthToken('1234', $user->getRoles());
        $token->setUser($user);

        $session->set('_security_main_context', serialize($token));
        $session->save();

        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $client->request(Request::METHOD_GET, '/evenements');
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

    protected function init(array $fixtures = [], string $host = Config::APP_HOST)
    {
        if ($fixtures) {
            $this->loadFixtures($fixtures);
        }

        $this->client = $this->makeClient(false, ['HTTP_HOST' => $host]);
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
