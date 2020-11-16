<?php

namespace Tests\App\Controller\EnMarche\Filesystem;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class FileControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideAdherentsWithNoAccess
     */
    public function testAdherentCannotDownloadFile(string $adherentEmail)
    {
        $image = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $this->client->request(Request::METHOD_GET, \sprintf('/documents/%s', $image->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testAdminCannotDownloadFileIfNoPermission()
    {
        $image = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdmin($this->client, 'writer@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/documents/%s', $image->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCannotDownloadDirectory()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'images']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        self::assertStringContainsStringIgnoringCase('Directory cannot be download.', $this->client->getResponse()->getContent());
    }

    public function testDownloadExternalLink()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'dpt link for all']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        self::assertClientIsRedirectedTo('https://dpt.en-marche.fr', $this->client);
    }

    public function testCannotDownloadFileIfNoOnStorage()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        self::assertStringContainsStringIgnoringCase('No file found in storage for this File.', $this->client->getResponse()->getContent());
    }

    public function provideAdherentsWithNoAccess(): iterable
    {
        yield ['benjyd@aol.com'];
        yield ['jacques.picard@en-marche.fr'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
