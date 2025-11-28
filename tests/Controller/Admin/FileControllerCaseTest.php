<?php

declare(strict_types=1);

namespace Tests\App\Controller\Admin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractAdminWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class FileControllerCaseTest extends AbstractAdminWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideAdherentsWithNoAccess')]
    public function testAdherentCannotDownloadFileIfNoPermission(string $adherentEmail)
    {
        $image = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $this->client->request(Request::METHOD_GET, \sprintf('/filesystem/documents/%s', $image->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testAdminCannotDownloadFileIfNoPermission()
    {
        $image = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdmin($this->client, 'writer@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/filesystem/documents/%s', $image->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCannotDownloadDirectory()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'images']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/filesystem/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testDownloadExternalLink()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'dpt link for all']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/filesystem/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        self::assertClientIsRedirectedTo('https://dpt.en-marche.fr', $this->client);
    }

    public function testCannotDownloadFileIfNoOnStorage()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, \sprintf('/filesystem/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public static function provideAdherentsWithNoAccess(): iterable
    {
        yield ['benjyd@aol.com'];
        yield ['jacques.picard@en-marche.fr'];
    }
}
