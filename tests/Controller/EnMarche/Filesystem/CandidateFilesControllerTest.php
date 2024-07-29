<?php

namespace Tests\App\Controller\EnMarche\Filesystem;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class CandidateFilesControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testFilesListIsDisplayedSuccessfullyForDepartmentalCandidate(): void
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents');

        $files = $crawler->filter('tbody tr');

        self::assertSame(4, $files->count());
        self::assertSame(3, $crawler->filter('tbody tr:contains("Dossier")')->count());
        self::assertSame(1, $crawler->filter('tbody tr:contains("Lien externe")')->count());
        self::assertStringContainsString('documents/', $files->eq(0)->text());
        self::assertStringContainsString('dpt link', $files->eq(1)->text());
        self::assertStringContainsString('images/', $files->eq(2)->text());
        self::assertStringContainsString('videos/', $files->eq(3)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/documents');

        $files = $crawler->filter('tbody tr');

        self::assertSame(1, $files->count());
        self::assertStringContainsString('PDF for all.pdf', $files->eq(0)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/images');

        $files = $crawler->filter('tbody tr');

        self::assertSame(3, $files->count());
        self::assertStringContainsString('Image for all.png', $files->eq(0)->text());
        self::assertStringContainsString('Image for candidates.jpg', $files->eq(1)->text());
        self::assertStringContainsString('Image for departmental candidates.jpg', $files->eq(2)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/videos');

        $files = $crawler->filter('tbody tr');

        self::assertSame(1, $files->count());
        self::assertStringContainsString('dpt link for all', $files->eq(0)->text());
    }

    public function testFilesListIsDisplayedSuccessfullyForLeaderRegionalCandidate(): void
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents');

        $files = $crawler->filter('tbody tr');

        self::assertSame(4, $files->count());
        self::assertSame(3, $crawler->filter('tbody tr:contains("Dossier")')->count());
        self::assertSame(1, $crawler->filter('tbody tr:contains("Lien externe")')->count());
        self::assertStringContainsString('documents/', $files->eq(0)->text());
        self::assertStringContainsString('dpt link', $files->eq(1)->text());
        self::assertStringContainsString('images/', $files->eq(2)->text());
        self::assertStringContainsString('videos/', $files->eq(3)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/documents');

        $files = $crawler->filter('tbody tr');

        self::assertSame(2, $files->count());
        self::assertStringContainsString('external link for regional candidates', $files->eq(0)->text());
        self::assertStringContainsString('PDF for all.pdf', $files->eq(1)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/images');

        $files = $crawler->filter('tbody tr');

        self::assertSame(2, $files->count());
        self::assertStringContainsString('Image for all.png', $files->eq(0)->text());
        self::assertStringContainsString('Image for candidates.jpg', $files->eq(1)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/videos');

        $files = $crawler->filter('tbody tr');

        self::assertSame(1, $files->count());
        self::assertStringContainsString('dpt link for all', $files->eq(0)->text());
    }

    public function testFilesListIsDisplayedSuccessfullyForHeadedRegionalCandidate(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents');

        $files = $crawler->filter('tbody tr');

        self::assertSame(4, $files->count());
        self::assertSame(3, $crawler->filter('tbody tr:contains("Dossier")')->count());
        self::assertSame(1, $crawler->filter('tbody tr:contains("Lien externe")')->count());
        self::assertStringContainsString('documents/', $files->eq(0)->text());
        self::assertStringContainsString('dpt link', $files->eq(1)->text());
        self::assertStringContainsString('images/', $files->eq(2)->text());
        self::assertStringContainsString('videos/', $files->eq(3)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/documents');

        $files = $crawler->filter('tbody tr');

        self::assertSame(2, $files->count());
        self::assertStringContainsString('external link for regional candidates', $files->eq(0)->text());
        self::assertStringContainsString('PDF for all.pdf', $files->eq(1)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/images');

        $files = $crawler->filter('tbody tr');

        self::assertSame(2, $files->count());
        self::assertStringContainsString('Image for all.png', $files->eq(0)->text());
        self::assertStringContainsString('Image for candidates.jpg', $files->eq(1)->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/documents/videos');

        $files = $crawler->filter('tbody tr');

        self::assertSame(1, $files->count());
        self::assertStringContainsString('dpt link for all', $files->eq(0)->text());
    }

    #[DataProvider('getFiles')]
    public function testFilesListIsDisplayedSuccessfully(string $name, string $userEmail, int $statusCode): void
    {
        $this->authenticateAsAdherent($this->client, $userEmail);

        $file = $this->getFileRepository()->findOneBy(['name' => $name]);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/espace-candidat/documents/%s', $file->getUuid())
        );

        self::assertResponseStatusCode($statusCode, $this->client->getResponse());
    }

    public static function getFiles(): array
    {
        return [
            ['external hidden link', 'jacques.picard@en-marche.fr', Response::HTTP_FORBIDDEN],
            ['external hidden link', 'luciole1989@spambox.fr', Response::HTTP_FORBIDDEN],
            ['external hidden link', 'francis.brioul@yahoo.com', Response::HTTP_FORBIDDEN],
            ['Image for candidates', 'jacques.picard@en-marche.fr', Response::HTTP_NOT_FOUND],
            ['Image for candidates', 'luciole1989@spambox.fr', Response::HTTP_NOT_FOUND],
            ['Image for candidates', 'francis.brioul@yahoo.com', Response::HTTP_NOT_FOUND],
            ['Image for departmental candidates', 'jacques.picard@en-marche.fr', Response::HTTP_FORBIDDEN],
            ['Image for departmental candidates', 'luciole1989@spambox.fr', Response::HTTP_FORBIDDEN],
            ['Image for departmental candidates', 'francis.brioul@yahoo.com', Response::HTTP_NOT_FOUND],
            ['dpt link for all', 'jacques.picard@en-marche.fr', Response::HTTP_FOUND],
            ['dpt link for all', 'luciole1989@spambox.fr', Response::HTTP_FOUND],
            ['dpt link for all', 'francis.brioul@yahoo.com', Response::HTTP_FOUND],
            ['external link for regional candidates', 'jacques.picard@en-marche.fr', Response::HTTP_FOUND],
            ['external link for regional candidates', 'luciole1989@spambox.fr', Response::HTTP_FOUND],
            ['external link for regional candidates', 'francis.brioul@yahoo.com', Response::HTTP_FORBIDDEN],
            ['PDF for all', 'jacques.picard@en-marche.fr', Response::HTTP_NOT_FOUND],
            ['PDF for all', 'luciole1989@spambox.fr', Response::HTTP_NOT_FOUND],
            ['PDF for all', 'francis.brioul@yahoo.com', Response::HTTP_NOT_FOUND],
            ['external link for regional candidates', 'jacques.picard@en-marche.fr', Response::HTTP_FOUND],
            ['external link for regional candidates', 'luciole1989@spambox.fr', Response::HTTP_FOUND],
            ['external link for regional candidates', 'francis.brioul@yahoo.com', Response::HTTP_FORBIDDEN],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
