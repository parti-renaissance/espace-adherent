<?php

namespace Tests\App\Controller;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tests\App\TestHelperTrait;

/**
 * @method static assertSame($expected, $actual, $message = '')
 */
trait ControllerTestTrait
{
    use TestHelperTrait;

    public function assertResponseStatusCode(int $statusCode, Response $response, string $message = ''): void
    {
        self::assertSame($statusCode, $response->getStatusCode(), $message);
    }

    public function assertClientIsRedirectedTo(
        string $path,
        KernelBrowser $client,
        bool $withSchemes = false,
        bool $permanent = false,
        bool $withParameters = true,
    ): void {
        $response = $client->getResponse();

        $this->assertResponseStatusCode($permanent ? Response::HTTP_MOVED_PERMANENTLY : Response::HTTP_FOUND, $response);

        $currentUrl = $response->headers->get('location');

        $this->assertSame(
            $withSchemes ? $client->getRequest()->getSchemeAndHttpHost().$path : $path,
            $withParameters ? $currentUrl : substr($currentUrl, 0, strpos($currentUrl, '?'))
        );
    }

    public function assertStatusCode(int $code, KernelBrowser $client): void
    {
        $this->assertResponseStatusCode($code, $client->getResponse());
    }

    public function isSuccessful(Response $response): void
    {
        static::assertTrue($response->isSuccessful());
    }

    public function logout(KernelBrowser $client): void
    {
        $client->getContainer()->get('security.untracked_token_storage')->reset();
    }

    public function authenticateAsAdherent(KernelBrowser $client, string $emailAddress): void
    {
        if (!$user = $this->getAdherentRepository()->findOneBy(['emailAddress' => $emailAddress])) {
            throw new \Exception(\sprintf('Adherent %s not found', $emailAddress));
        }

        $this->authenticate($client, $user);
    }

    public function authenticateAsAdmin(KernelBrowser $client, string $email = 'admin@en-marche-dev.fr'): void
    {
        if (!$user = $this->getAdministratorRepository()->loadUserByIdentifier($email)) {
            throw new \Exception(\sprintf('Admin %s not found', $email));
        }

        $this->authenticate($client, $user);
    }

    protected function authenticateAsAdherentWithChoosingSpace(string $email, string $spaceLinkName): void
    {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request(Request::METHOD_GET, '/');

        self::assertStringContainsString($spaceLinkName, $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());
        $this->client->click($crawler->selectLink($spaceLinkName)->link());

        $path = '';
        if (0 == strpos($spaceLinkName, 'Espace candidat')) {
            $path = '/espace-candidat/utilisateurs';
        }
        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($path, $this->client);
        $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
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

    protected function seeFlashMessage(Crawler $crawler, ?string $message = null, string $level = 'info'): bool
    {
        $flash = $crawler->filter('.flash--'.$level);

        if ($message) {
            self::assertSame($message, trim($flash->text()));
        }

        return 1 === \count($flash);
    }

    protected function appendCollectionFormPrototype(
        \DOMElement $collection,
        string $newIndex = '0',
        string $prototypeName = '__name__',
    ): void {
        $prototypeHTML = $collection->getAttribute('data-prototype');
        $prototypeHTML = str_replace($prototypeName, $newIndex, $prototypeHTML);
        $prototypeFragment = new \DOMDocument();
        $prototypeFragment->loadHTML($prototypeHTML);
        foreach ($prototypeFragment->getElementsByTagName('body')->item(0)->childNodes as $prototypeNode) {
            $collection->appendChild($collection->ownerDocument->importNode($prototypeNode, true));
        }
    }

    protected function assertSeeCommitteeTimelineMessage(
        Crawler $crawler,
        int $position,
        string $author,
        string $role,
        string $text,
    ): void {
        $message = $crawler->filter('.committee__timeline__message')->eq($position);

        $this->assertStringContainsString($author, $message->filter('h3')->text());
        $this->assertSame($role, $message->filter('h3 span')->text());
        $this->assertStringContainsString($text, $message->filter('div')->first()->text());
    }

    private function authenticate(KernelBrowser $client, UserInterface $user): void
    {
        $client->loginUser($user, 'main_context');
    }

    private static function assertAdherentHasZone(Adherent $adherent, string $code): void
    {
        $zone = $adherent
            ->getZones()
            ->filter(function (Zone $zone) use ($code) {
                return $code === $zone->getCode();
            })
            ->first()
        ;

        self::assertInstanceOf(Zone::class, $zone);
        self::assertSame($zone->getCode(), $code);
    }

    protected function getMessageRecorder(): MessageRecorderInterface
    {
        return $this->client->getContainer()->get(MessageRecorderInterface::class);
    }

    protected function getUrl(
        string $route,
        array $params = [],
        int $absolute = UrlGeneratorInterface::ABSOLUTE_PATH,
    ): string {
        return $this->get('router')->generate($route, $params, $absolute);
    }

    protected function transformToArray(string $encodedData): array
    {
        $tmpHandle = tmpfile();
        fwrite($tmpHandle, $encodedData);
        $metaDatas = stream_get_meta_data($tmpHandle);
        $tmpFilename = $metaDatas['uri'];

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tmpFilename);

        return $spreadsheet->getActiveSheet()->toArray();
    }

    protected function assertArrayContainsIgnoringNumericKeys(array $subset, array $array): void
    {
        $filteredArray = array_filter(
            $array,
            fn ($item) => \in_array($item, $subset, true)
        );

        $this->assertCount(\count($subset), $filteredArray);
    }
}
