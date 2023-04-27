<?php

namespace Tests\App\Controller\EnMarche\Filesystem;

use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebCaseTest;
use Tests\App\Controller\ControllerTestTrait;

class FilesControllerTest extends AbstractEnMarcheWebCaseTest
{
    use ControllerTestTrait;

    /**
     * @dataProvider getSpaceURIPrefixes
     */
    public function testFilesListIsDisplayedSuccessfully(
        string $uriPrefix,
        string $userEmail,
        int $expectedDocumentsNumber
    ): void {
        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, $uriPrefix.'/documents');

        self::assertSame($expectedDocumentsNumber, $crawler->filter('tbody tr')->count());
    }

    public function getSpaceURIPrefixes(): array
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
