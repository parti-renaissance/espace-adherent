<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventCategory;
use AppBundle\Entity\ReferentTag;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
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

    public function assertClientIsRedirectedTo(string $path, Client $client, bool $withSchemes = false, bool $permanent = false)
    {
        $response = $client->getResponse();

        $this->assertResponseStatusCode($permanent ? Response::HTTP_MOVED_PERMANENTLY : Response::HTTP_FOUND, $response);
        $this->assertSame(
            $withSchemes ? $client->getRequest()->getSchemeAndHttpHost().$path : $path,
            $response->headers->get('location')
        );
    }

    public function logout(Client $client): Crawler
    {
        $client->request(Request::METHOD_GET, '/deconnexion');

        return $client->followRedirect();
    }

    public function authenticateAsAdherent(Client $client, string $emailAddress, string $password = LoadAdherentData::DEFAULT_PASSWORD): Crawler
    {
        $crawler = $client->request(Request::METHOD_GET, '/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => $emailAddress,
            '_login_password' => $password,
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

    public function authenticateAsAdmin(Client $client, string $emailAddress = 'admin@en-marche-dev.fr', string $password = 'admin'): Crawler
    {
        $crawler = $client->request(Request::METHOD_GET, '/admin/login');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => $emailAddress,
            '_login_password' => $password,
        ]));

        $shouldBeRedirectedTo = 'http://'.$this->hosts['app'].'/admin/dashboard';

        if ($shouldBeRedirectedTo !== $client->getResponse()->headers->get('location')) {
            $this->fail(
                'Authentication as '.$emailAddress.' failed: check the credentials used in authenticateAsAdmin() '.
                'and ensure you are properly loading adherents fixtures.'
            );
        }

        return $client->followRedirect();
    }

    protected function getFirstPrefixForm(Form $form): ?string
    {
        foreach ($form->all() as $field) {
            preg_match('/^(.*)\[.*\]$/', $field->getName(), $matches);
            if ($matches) {
                return $matches[1];
            }
        }

        return null;
    }

    protected function seeFlashMessage(Crawler $crawler, ?string $message = null): bool
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
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

    protected function assertSeeCommitteeTimelineMessage(Crawler $crawler, int $position, string $author, string $role, string $text)
    {
        $message = $crawler->filter('.committee__timeline__message')->eq($position);

        $this->assertContains($author, $message->filter('h3')->text());
        $this->assertSame($role, $message->filter('h3 span')->text());
        $this->assertContains($text, $message->filter('div')->first()->text());
    }

    private function getEventCategoryIdForName(string $categoryName): int
    {
        return $this->manager->getRepository(EventCategory::class)->findOneBy(['name' => $categoryName])->getId();
    }

    private static function assertAdherentHasReferentTag(Adherent $adherent, string $code): void
    {
        $referentTag = $adherent
            ->getReferentTags()
            ->filter(function (ReferentTag $referentTag) use ($code) {
                return $code === $referentTag->getCode();
            })
            ->first()
        ;

        self::assertInstanceOf(ReferentTag::class, $referentTag);
        self::assertSame($referentTag->getCode(), $code);
    }

    protected function init(array $fixtures = [], string $host = 'app')
    {
        $this->loadFixtures($fixtures);

        $this->container = $this->getContainer();

        $this->hosts = [
            'scheme' => $this->container->getParameter('router.request_context.scheme'),
            'app' => $this->container->getParameter('app_host'),
            'amp' => $this->container->getParameter('amp_host'),
            'legislatives' => $this->container->getParameter('legislatives_host'),
        ];

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
