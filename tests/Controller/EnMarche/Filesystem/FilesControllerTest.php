<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche\Filesystem;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class FilesControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('getSpaceURIPrefixes')]
    public function testFilesListIsDisplayedSuccessfully(
        string $uriPrefix,
        string $userEmail,
        int $expectedDocumentsNumber,
    ): void {
        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, $uriPrefix.'/documents');

        self::assertSame($expectedDocumentsNumber, $crawler->filter('tbody tr')->count());
    }

    public static function getSpaceURIPrefixes(): array
    {
        return [
            ['/espace-candidat', 'jacques.picard@en-marche.fr', 4],
            ['/espace-candidat', 'luciole1989@spambox.fr', 4],
            ['/espace-candidat', 'francis.brioul@yahoo.com', 4],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
