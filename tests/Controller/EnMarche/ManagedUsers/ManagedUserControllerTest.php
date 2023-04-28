<?php

namespace Tests\App\Controller\EnMarche\ManagedUsers;

use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebCaseTest;
use Tests\App\Controller\ControllerTestTrait;

class ManagedUserControllerTest extends AbstractEnMarcheWebCaseTest
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
