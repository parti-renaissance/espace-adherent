<?php

namespace AppBundle\Command;

use AppBundle\Content\MediaFactory;
use AppBundle\Entity\Media;
use AppBundle\Entity\Referent;
use AppBundle\Repository\MediaRepository;
use AppBundle\Repository\ReferentRepository;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as HttpFileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;

class ImportReferentBioPictureCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'app:import:referent-bio-picture';
    const CSV_FILENAME = 'referents_bio_photo.csv';
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ReferentRepository
     */
    private $referentRepository;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaFactory
     */
    private $mediaFactory;

    /**
     * @var Filesystem
     */
    private $storage;

    private $pathExtractedFile;
    private $referentNotFound = [];
    private $imageError = [];
    private $imageAddedOnStorage = [];

    protected function configure()
    {
        $this
          ->setName(self::COMMAND_NAME)
          ->addArgument('fileUrl', InputArgument::REQUIRED)
          ->setDescription('Import bio & picture for referent already in DB')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->referentRepository = $this->em->getRepository(Referent::class);
        $this->mediaRepository = $this->em->getRepository(Media::class);
        $this->mediaFactory = $this->getContainer()->get(MediaFactory::class);
        $this->storage = $this->getContainer()->get('app.storage');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->pathExtractedFile = $this->extractArchive($input->getArgument('fileUrl'));
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage().' '.$exception->getCode());

            return 1;
        }

        try {
            $rows = $this->parseCSV(sprintf('%s%s', $this->pathExtractedFile, self::CSV_FILENAME));
        } catch (FileNotFoundException $exception) {
            $output->writeln($exception->getMessage());

            return 1;
        }

        $this->em->beginTransaction();

        $this->addBioAndPictureToReferent($rows);

        $this->em->flush();
        $this->em->commit();

        $output->writeln('Import OK');
        $output->writeln('');

        if (\count($this->referentNotFound)) {
            $output->writeln('The following referents are not found in database');
            foreach ($this->referentNotFound as $referentName) {
                $output->writeln($referentName);
            }
            $output->writeln('');
        }

        if (\count($this->imageError)) {
            $output->writeln('The image name are not found in zip archive OR can\'t upload it on storage');
            foreach ($this->imageError as $imageName) {
                $output->writeln($imageName);
            }
            $output->writeln('');
        }
    }

    private function extractArchive($pahToArchive): string
    {
        $zip = new ZipArchive();

        if ($statusCode = (true !== $zip->open($pahToArchive))) {
            throw new \Exception($this->zipStatusString($statusCode), $statusCode);
        }

        $pathToExtract = sprintf('%s/%s', sys_get_temp_dir(), uniqid('referent_bio_picture'));

        if (!$zip->extractTo($pathToExtract)) {
            throw new \Exception('Error during extracting data from archive');
        }
        $zip->close();

        return $pathToExtract.'/';
    }

    private function zipStatusString(int $status): string
    {
        switch ($status) {
            case ZipArchive::ER_MULTIDISK: return 'Multi-disk zip archives not supported';
            case ZipArchive::ER_RENAME: return 'Renaming temporary file failed';
            case ZipArchive::ER_CLOSE: return 'Closing zip archive failed';
            case ZipArchive::ER_SEEK: return 'Seek error';
            case ZipArchive::ER_READ: return 'Read error';
            case ZipArchive::ER_WRITE: return 'Write error';
            case ZipArchive::ER_CRC: return 'CRC error';
            case ZipArchive::ER_ZIPCLOSED: return 'Containing zip archive was closed';
            case ZipArchive::ER_NOENT: return 'No such file';
            case ZipArchive::ER_EXISTS: return 'File already exists';
            case ZipArchive::ER_OPEN: return 'Can\'t open file';
            case ZipArchive::ER_TMPOPEN: return 'Failure to create temporary file';
            case ZipArchive::ER_ZLIB: return 'Zlib error';
            case ZipArchive::ER_MEMORY: return 'Malloc failure';
            case ZipArchive::ER_CHANGED: return 'Entry has been changed';
            case ZipArchive::ER_COMPNOTSUPP: return 'Compression method not supported';
            case ZipArchive::ER_EOF: return 'Premature EOF';
            case ZipArchive::ER_INVAL: return 'Invalid argument';
            case ZipArchive::ER_NOZIP: return 'Not a zip archive';
            case ZipArchive::ER_INTERNAL: return 'Internal error';
            case ZipArchive::ER_INCONS: return 'Zip archive inconsistent';
            case ZipArchive::ER_REMOVE: return 'Can\'t remove file';
            case ZipArchive::ER_DELETED: return 'Entry has been deleted';

            default: return sprintf('Unknown status %s', $status);
        }
    }

    private function parseCSV(string $filename): array
    {
        $rows = [];
        if (false === ($handle = @fopen($filename, 'r'))) {
            throw new FileNotFoundException(sprintf('%s not found', $filename));
        }

        $firstline = true;

        while (false !== ($data = fgetcsv($handle, 10000, ';'))) {
            if ($firstline) {
                $firstline = false;

                continue;
            }

            $row = array_map('trim', $data);
            $rows[] = [
              'id' => $row[0],
              'first_name' => $row[1],
              'last_name' => $row[2],
              'bio' => $row[3],
              'image' => $row[4],
            ];
        }
        fclose($handle);

        return $rows;
    }

    private function persistMedia(string $name, string $path): ? Media
    {
        if ($media = $this->mediaRepository->findOneByName($name)) {
            return $media;
        }

        try {
            $mediaFile = new File(sprintf('%s/%s', $this->pathExtractedFile, $path));
        } catch (HttpFileNotFoundException $e) {
            $this->imageError[] = sprintf('Image not found : %s %s', $name, $path);

            return null;
        }

        if (!$this->storage->put('images/'.$path, file_get_contents($mediaFile->getPathname()))) {
            $this->imageError[] = sprintf('Image can\'t be upload on storage : %s %s', $name, $path);

            return null;
        }

        $this->imageAddedOnStorage[] = 'images/'.$path;
        $media = $this->mediaFactory->createFromFile($name, $path, $mediaFile);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    private function addBioAndPictureToReferent(array $rows): void
    {
        foreach ($rows as $row) {
            if (!$referent = $this->referentRepository->findOneBy(['firstName' => $row['first_name'], 'lastName' => $row['last_name']])) {
                $this->referentNotFound[] = sprintf('%s - %s %s', $row['id'], $row['first_name'], $row['last_name']);

                continue;
            }

            $referent->setDescription($row['bio']);

            if ($media = $this->persistMedia(sprintf('%s %s', $row['first_name'], $row['last_name']), $row['image'])) {
                $referent->setMedia($media);
            }

            $this->em->persist($referent);
        }
    }

    public function __destruct()
    {
        if (!\count($this->imageAddedOnStorage)) {
            return;
        }

        foreach ($this->imageAddedOnStorage as $imgePath) {
            if (!$this->storage->has($imgePath)) {
                continue;
            }

            $this->storage->delete($imgePath);
        }
    }
}
