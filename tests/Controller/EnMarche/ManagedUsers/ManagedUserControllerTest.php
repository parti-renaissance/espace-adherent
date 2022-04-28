<?php

namespace Tests\App\Controller\EnMarche\ManagedUsers;

use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class ManagedUserControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider getSpaceURIPrefixes
     */
    public function testManagedUsersListIsDisplayedSuccessfully(
        string $uriPrefix,
        string $userEmail,
        int $expectedAdherentNumber
    ): void {
        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, $uriPrefix.'/utilisateurs');

        self::assertSame($expectedAdherentNumber, $crawler->filter('tbody tr')->count());
    }

    public function getSpaceURIPrefixes(): array
    {
        return [
            ['/espace-referent', 'referent@en-marche-dev.fr', 4],
            ['/espace-depute', 'deputy@en-marche-dev.fr', 2],
            ['/espace-senateur', 'senateur@en-marche-dev.fr', 1],
            ['/espace-candidat', 'jacques.picard@en-marche.fr', 4],
        ];
    }

    /**
     * @dataProvider provideSpaces
     */
    public function testDifferentSpaceCanBeDelegated(
        string $email,
        string $spaceLabel,
        string $uriPrefix,
        int $expectedDocumentsNumber
    ) {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request('GET', '/');
        self::assertStringContainsString($spaceLabel, $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());

        $this->client->click($crawler->selectLink($spaceLabel)->link());
        $this->assertResponseStatusCode(302, $this->client->getResponse());

        $this->client->followRedirect();

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, $uriPrefix.'/utilisateurs');

        self::assertSame($expectedDocumentsNumber, $crawler->filter('tbody tr')->count());
    }

    public function provideSpaces(): \Generator
    {
        yield ['gisele-berthoux@caramail.com', 'Espace candidat partagé (Île-de-France)', '/espace-candidat', 1];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
