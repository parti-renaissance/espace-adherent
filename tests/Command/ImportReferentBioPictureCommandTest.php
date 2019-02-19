<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\ImportReferentBioPictureCommand;
use AppBundle\Entity\Media;
use AppBundle\Entity\Referent;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group command
 */
class ImportReferentBioPictureCommandTest extends WebTestCase
{
    use ControllerTestTrait;
    const VALID_ARCHIVE_NAME = 'correct.zip';
    const ARCHIVE_WITHOUT_CSV_NAME = 'archive_without_csv.zip';
    const ARCHIVE_WITH_NOT_REFERENT_IN_DB = 'archive_with_not_referent_in_db.zip';
    const ARCHIVE_WITH_MISSING_IMAGE_FILE = 'archive_with_missing_image_file.zip';
    const ARCHIVES_NAME = [
        self::VALID_ARCHIVE_NAME,
        self::ARCHIVE_WITHOUT_CSV_NAME,
        self::ARCHIVE_WITH_NOT_REFERENT_IN_DB,
        self::ARCHIVE_WITH_MISSING_IMAGE_FILE,
    ];

    public function testCommandSucessImport()
    {
        $this->createCorrectZipArchive();
        $output = $this->runCommand(ImportReferentBioPictureCommand::COMMAND_NAME, ['fileUrl' => self::VALID_ARCHIVE_NAME]);

        $referentRepository = $this->getRepository(Referent::class);
        $mediaRepository = $this->getRepository(Media::class);

        $referent1 = $referentRepository->findOneBy(['firstName' => 'Nicolas', 'lastName' => 'Bordes']);
        $referent2 = $referentRepository->findOneBy(['firstName' => 'Alban', 'lastName' => 'Martin']);

        $this->assertContains('Import OK', $output);
        $this->assertNotNull($mediaRepository->findOneByName('Nicolas Bordes'));
        $this->assertNotNull($mediaRepository->findOneByName('Alban Martin'));
        $this->assertNotNull($referent1->getMedia());
        $this->assertNotNull($referent2->getMedia());
        $this->assertContains('Nicolas Bordes Le Lorem Ipsum est simplement du faux texte', $referent1->getDescription());
        $this->assertContains('Alban Martin Le Lorem Ipsum est simplement du faux texte', $referent2->getDescription());
    }

    public function testCommandWithoutCsvInArchive()
    {
        $this->createZipArchiveWithoutCsv();
        $output = $this->runCommand(ImportReferentBioPictureCommand::COMMAND_NAME, ['fileUrl' => self::ARCHIVE_WITHOUT_CSV_NAME]);

        $this->assertContains('csv not found', $output);
    }

    public function testCommandWithNotExistReferentInDb()
    {
        $this->createZipArchiveWithMissingDbReferentName();
        $output = $this->runCommand(ImportReferentBioPictureCommand::COMMAND_NAME, ['fileUrl' => self::ARCHIVE_WITH_NOT_REFERENT_IN_DB]);

        $this->assertContains('The following referents are not found in database', $output);
        $this->assertContains('3 - Toto Tata', $output);
    }

    public function testCommandWithNotFoundImageFileInArchive()
    {
        $this->createZipArchiveWithMissingImageFile();
        $output = $this->runCommand(ImportReferentBioPictureCommand::COMMAND_NAME, ['fileUrl' => self::ARCHIVE_WITH_MISSING_IMAGE_FILE]);

        $referentRepository = $this->getRepository(Referent::class);
        $mediaRepository = $this->getRepository(Media::class);

        $referent1 = $referentRepository->findOneBy(['firstName' => 'Nicolas', 'lastName' => 'Bordes']);
        $referent2 = $referentRepository->findOneBy(['firstName' => 'Alban', 'lastName' => 'Martin']);

        $this->assertContains('The image name are not found in zip archive OR can\'t upload it on storage', $output);
        $this->assertContains('Image not found : Nicolas Bordes missing_file.jpg', $output);
        $this->assertNotNull($mediaRepository->findOneByPath('alban_martin.jpg'));
        $this->assertNull($mediaRepository->findOneByPath('missing_file.jpg'));
        $this->assertNull($referent1->getMedia());
        $this->assertNotNull($referent1->getDescription());
        $this->assertNotNull($referent2->getMedia());
    }

    private function createCorrectZipArchive(): void
    {
        $archive = new \ZipArchive();

        $archive->open(self::VALID_ARCHIVE_NAME, \ZipArchive::CREATE);
        $pathToImage = $this->getContainer()->getParameter('kernel.project_dir').'/data/dist/';
        $archive->addFromString('nicolas_bordes.jpg', file_get_contents($pathToImage.'macron.jpg'));
        $archive->addFromString('alban_martin.jpg', file_get_contents($pathToImage.'richardferrand.jpg'));
        $csvData = [
            ['id', 'first_name', 'last_name', 'bio', 'image'],
            ['1', 'Nicolas', 'Bordes', 'Nicolas Bordes Le Lorem Ipsum est simplement du faux texte', 'nicolas_bordes.jpg'],
            ['2', 'Alban', 'Martin', 'Alban Martin Le Lorem Ipsum est simplement du faux texte', 'alban_martin.jpg'],
        ];
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(';', array_map(function ($row) {
                return \is_int($row) ? $row : sprintf('"%s"', $row);
            }, $row)).\PHP_EOL;
        }
        $archive->addFromString(ImportReferentBioPictureCommand::CSV_FILENAME, $csvContent);

        $archive->close();
    }

    private function createZipArchiveWithoutCsv(): void
    {
        $archive = new \ZipArchive();

        $archive->open(self::ARCHIVE_WITHOUT_CSV_NAME, \ZipArchive::CREATE);
        $pathToImage = $this->getContainer()->getParameter('kernel.project_dir').'/data/dist/';
        $archive->addFromString('nicolas_bordes.jpg', file_get_contents($pathToImage.'macron.jpg'));
        $archive->addFromString('alban_martin.jpg', file_get_contents($pathToImage.'richardferrand.jpg'));

        $archive->close();
    }

    private function createZipArchiveWithMissingDbReferentName(): void
    {
        $archive = new \ZipArchive();

        $archive->open(self::ARCHIVE_WITH_NOT_REFERENT_IN_DB, \ZipArchive::CREATE);
        $csvData = [
            ['id', 'first_name', 'last_name', 'bio', 'image'],
            ['3', 'Toto', 'Tata', 'Toto Tata Alban Martin Le Lorem Ipsum est simplement du faux texte', 'toto.jpg'],
        ];
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(';', array_map(function ($row) {
                return \is_int($row) ? $row : sprintf('"%s"', $row);
            }, $row)).\PHP_EOL;
        }
        $archive->addFromString(ImportReferentBioPictureCommand::CSV_FILENAME, $csvContent);

        $archive->close();
    }

    private function createZipArchiveWithMissingImageFile(): void
    {
        $archive = new \ZipArchive();

        $archive->open(self::ARCHIVE_WITH_MISSING_IMAGE_FILE, \ZipArchive::CREATE);
        $pathToImage = $this->getContainer()->getParameter('kernel.project_dir').'/data/dist/';
        $archive->addFromString('alban_martin.jpg', file_get_contents($pathToImage.'richardferrand.jpg'));
        $csvData = [
            ['id', 'first_name', 'last_name', 'bio', 'image'],
            ['1', 'Nicolas', 'Bordes', 'Nicolas Bordes Le Lorem Ipsum est simplement du faux texte', 'missing_file.jpg'],
            ['2', 'Alban', 'Martin', 'Alban Martin Le Lorem Ipsum est simplement du faux texte', 'alban_martin.jpg'],
        ];
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(';', array_map(function ($row) {
                return \is_int($row) ? $row : sprintf('"%s"', $row);
            }, $row)).\PHP_EOL;
        }
        $archive->addFromString(ImportReferentBioPictureCommand::CSV_FILENAME, $csvContent);

        $archive->close();
    }

    public function setUp()
    {
        $this->container = $this->getContainer();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        foreach (self::ARCHIVES_NAME as $archiveName) {
            if (file_exists($archiveName)) {
                unlink($archiveName);
            }
        }

        parent::tearDown();
    }
}
