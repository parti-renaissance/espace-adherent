<?php

namespace Tests\App\Controller\EnMarche\ManagedUsers;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class ManagedUserControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('getSpaceURIPrefixes')]
    public function testManagedUsersListIsDisplayedSuccessfully(
        string $uriPrefix,
        string $userEmail,
        int $expectedAdherentNumber
    ): void {
        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, $uriPrefix.'/utilisateurs');

        self::assertSame($expectedAdherentNumber, $crawler->filter('tbody tr')->count());
    }

    public static function getSpaceURIPrefixes(): array
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
